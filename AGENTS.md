# AGENTS.md

Laravel 12 app (PHP 8.2+): Sanctum token API under `routes/api.php` + Blade/Alpine.js/Tailwind frontend. Recipes come from the external TheMealDB API. Local dev DB is **PostgreSQL** (`.env`), not SQLite.

## Commands

```bash
composer setup                     # full install: deps, .env, key, migrate, npm, build
composer dev                       # server + queue + pail + vite + scheduler concurrently
composer test                      # config:clear + php artisan test (PHPUnit)
php artisan test --filter=RecipeApiTest   # single test class/file
npm run build                      # vite build (force rebuild)
npm run test:e2e                   # reset+seed e2e DB, then Playwright
npx playwright test tests/e2e/login.spec.js --project=chromium  # single E2E spec (run npm run test:e2e:setup first)
php artisan app:mealdb:sync        # sync TheMealDB into local recipes table (re-runnable; --area=X for one cuisine)
```

No lint script; `laravel/pint` is installed (`vendor/bin/pint`) but not enforced in CI.

## Environment gotchas

- E2E requires a **running local PostgreSQL**. Copy `.env.e2e.example` → `.env.e2e` before the first Playwright run; adjust `DB_USERNAME` (it's hardcoded to `fatmahassan`).
- `npm run test:e2e` runs `migrate:fresh --seed --env=e2e`, which **wipes the `recipes_e2e` database**. Dev DB `recipes` is untouched.
- Local PHPUnit uses DB connection from `.env` (pgsql) with database `testing` (phpunit.xml only overrides `DB_DATABASE`) — create that DB locally. CI instead overrides to SQLite.
- `npm install` triggers `scripts/build-assets.js` (postinstall): it **skips the Vite build if `public/build/manifest.json` exists**, and on build failure silently writes a minimal CDN-fallback manifest. Stale assets? Run `npm run build` directly.

## Testing notes

- Playwright (`tests/e2e/`, POM in `pages/`) is the E2E tool. **Cypress is vestigial** — no real specs; ignore `cypress:*` scripts.
- Playwright auto-starts `php artisan serve --env=e2e` on :8000 and reuses an already-running server outside CI.
- `@smoke` tag selects the PR suite: `npx playwright test --grep @smoke`. CI runs chromium only; smoke on PRs, full suite on pushes.
- Feature tests use `RefreshDatabase` and mock TheMealDB with `Http::fake()` — no network needed.
- Newman API tests (`npx newman run postman/Recipes-*.json ...`) need the app served on `127.0.0.1:8000` with migrated+seeded DB first.

## Architecture / conventions

- External recipes: `app/Services/RecipeService.php` hits TheMealDB, cached 1h; returns `[]` on failure or "no results" (API returns `null` meals). Base URL in `config/services.php` (`THEMEALDB_BASE_URL`).
- Local recipe DB: `php artisan app:mealdb:sync` upserts TheMealDB into `recipes` + `recipe_ingredients` (hash-skipped, re-runnable). Daily 03:00 UTC schedule; runs locally via `composer dev` (schedule:work), on prod via cron-job.org hitting `POST /api/sync/mealdb` with `X-Sync-Token: MEALDB_SYNC_TOKEN` (empty token = endpoint 403s). Endpoint returns **202 instantly** and syncs after the response (30s webhook timeouts are harmless); append `?wait=1` for blocking stats; concurrent triggers get 409 (cache lock).
- Recipe read source: `RECIPE_SOURCE=api` (live TheMealDB) or `db` (local tables) — see `config/recipes.php`.
- Controllers are split: `app/Http/Controllers/` (web/Blade) vs `app/Http/Controllers/Api/` (JSON API).
- In `routes/api.php`, `/my-recipes` is registered **before** `/recipes/{id}` on purpose; recipe search is `POST /recipes/search` because it needs a request body. Keep this ordering when adding routes.
- API auth is Sanctum tokens (`HasApiTokens` on User, `auth:sanctum` middleware).

## CI

`.github/workflows/deploy.yml` runs on push/PR to `main`, `master`, `develop`: `phpunit` (composer test), `playwright`, `api-tests` (Newman). Reports are uploaded as artifacts on failure.

## Deployment (Render + Brevo)

- Production runs on Render (free tier) with Cloudflare DNS; domain: `dishoftoday.com`. DB is Supabase Postgres.
- Mail is Brevo SMTP (`smtp-relay.brevo.com`), domain-authenticated sender `noreply@dishoftoday.com`. **Port 587 times out from Render's egress — use `MAIL_PORT=465` + `MAIL_ENCRYPTION=ssl`.**
- Render env values are **literal, no interpolation**: set `MAIL_FROM_NAME=Dish of Today`, not `${APP_NAME}` (that works only in local `.env` via Dotenv).
- `APP_URL` must be `https://dishoftoday.com` on Render so password-reset links point to the domain.
- Do not enable Brevo's "Activate for SMTP keys" (IP allowlisting) — Render's shared egress IPs change; it would lock out mail sending.
- Brevo SMTP keys expire after 90 days of inactivity. Free tier: 300 emails/day.
- Saving env vars in Render triggers an auto-redeploy; free-tier services also sleep after ~15 min idle and need ~50 s to cold-start (looks like "site down").
