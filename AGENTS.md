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
- Local PHPUnit uses DB connection from `.env` (pgsql) with database `testing` (phpunit.xml only overrides `DB_DATABASE` and pins `THEMEALDB_MIRROR_IMAGES=false` so tests don't inherit local `.env` mirroring) — create that DB locally. CI instead overrides to SQLite.
- `npm install` triggers `scripts/build-assets.js` (postinstall): it **skips the Vite build if `public/build/manifest.json` exists**, and on build failure silently writes a minimal CDN-fallback manifest. Stale assets? Run `npm run build` directly.

## Testing notes

- Playwright (`tests/e2e/`, POM in `pages/`) is the E2E tool. **Cypress is vestigial** — no real specs; ignore `cypress:*` scripts.
- Playwright auto-starts `php artisan serve --env=e2e` on :8000 and reuses an already-running server outside CI.
- `@smoke` tag selects the PR suite: `npx playwright test --grep @smoke`. CI runs chromium only; smoke on PRs, full suite on pushes.
- Feature tests use `RefreshDatabase` and mock TheMealDB with `Http::fake()` — no network needed.
- Newman API tests (`npx newman run postman/Recipes-*.json ...`) need the app served on `127.0.0.1:8000` with migrated+seeded DB first.

## Architecture / conventions

- External recipes: `app/Services/RecipeService.php` hits TheMealDB, cached 1h; returns `[]` on failure or "no results" (API returns `null` meals). Base URL in `config/services.php` (`THEMEALDB_BASE_URL`).
- Local recipe DB: `php artisan app:mealdb:sync` upserts TheMealDB into `recipes` + `recipe_ingredients` (hash-skipped, re-runnable). Full sync enumerates via `search.php?f=a..z0..9` (uncapped on the premium key, full records, ~36 requests, no per-meal lookups); titles the letters can't reach (e.g. `Æbleskiver`) are reconciled by direct lookup — alive → upsert/reactivate, confirmed gone → deactivate, HTTP failure → sync aborts (never mass-deactivates on outage). `--area=X` still uses filter+lookup. Daily 03:00 UTC schedule; runs locally via `composer dev` (schedule:work), on prod via cron-job.org hitting `POST /api/sync/mealdb` with `X-Sync-Token: MEALDB_SYNC_TOKEN` (empty token = endpoint 403s). Endpoint is **blocking** (~10s with letter enumeration, fits cron-job.org's 30s timeout) and returns the stats JSON; concurrent triggers get 409 (cache lock).
- Image mirroring (TheMealDB approved image scraping, 2026-10): with `THEMEALDB_MIRROR_IMAGES=true`, sync downloads each meal thumb to `recipe-images/{external_id}.{ext}` on disk `THEMEALDB_IMAGE_DISK` (default `public`, needs `php artisan storage:link`; images download in pooled batches of `THEMEALDB_IMAGE_POOL_SIZE`, reusing `BATCH_SLEEP_MS`). `source_thumb_url` always keeps the original CDN URL; `thumb_url` is the display URL — **root-relative** (`/storage/recipe-images/…`) for local disks (host/port-portable; absolute `http://localhost/...` URLs from older syncs are repaired by the unchanged-meal branch on the next sync, without re-downloading) and absolute CDN URLs for remote disks (S3/Supabase). Design is **pessimistic-fallback**: an unconfirmed download (missing file, failed/HTTP-error/non-image response) keeps hotlinking the CDN, so mirroring problems never break images or fail the sync. Files are keyed deterministically per meal id, so only the **first** sync after enabling downloads everything (~793 images, one-time); later syncs skip existing files (stats: `images_mirrored`, `images_failed` in stats/endpoint JSON). Hash churn note: the `data_hash` includes `source_thumb_url`, so CDN image URL changes trigger re-download via the update branch.
- Saved recipes: `saved_recipes.recipe_internal_id` is a nullable FK to `recipes.id` (backfilled from `recipe_id` = TheMealDB id; orphan rows stay null; each sync **relinks orphans** via `BackfillSavedRecipeFk`). Display goes through `SavedRecipe::toMealDbArray()` — FK → presenter details, fallback to the `recipe_data` JSON snapshot (now nullable; still written on save as insurance).
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
- TheMealDB paid (supporter) key is live on prod: `THEMEALDB_BASE_URL` uses the **V2 path** (`https://www.themealdb.com/api/json/v2/<KEY>/`), `POOL_SIZE=10`, `BATCH_SLEEP_MS=0`, `RECIPE_SOURCE=db`. Never commit the paid key — `.env.example` keeps the free test key `1`.
- TheMealDB terms (verified 2026-10): API content may be scraped/copied via official endpoints; **image scraping/mirroring approved by TheMealDB (2026-10-13)**; artwork still requires mentioning TheMealDB as data source ("must" for paid keys) — owner plans a visible credit on a future About page (decided: **not** in the footer); sending debug pages is a secret-leak risk, so `APP_DEBUG=false` on prod.
- Enabling image mirroring on prod: local `public` disk works but Render's free-tier filesystem is **wiped on every deploy** — mirrored images would 404 until the next daily sync re-downloads them. Prefer `THEMEALDB_IMAGE_DISK=s3` pointed at Supabase Storage (S3-compatible): set `AWS_ENDPOINT=https://<project>.supabase.co/storage/v1/s3`, `AWS_URL=https://<project>.supabase.co/storage/v1/object/public/<bucket>`, `AWS_USE_PATH_STYLE_ENDPOINT=true` + keys. Mirroring is currently **off on prod** (local only).

## Open threads (2026-10-13)

The TheMealDB → own-DB migration is **complete and deployed** (normalized schema, sync service, repository seam, saved-recipe FK, seeder, blocking sync endpoint — cron verified 200 in ~10s). **Image mirroring is implemented** (2026-10-13, TheMealDB approved; verified locally: 793/793 images mirrored, second sync all-skipped) but **not yet enabled on prod**. Remaining items:

1. **Prod image mirroring enablement** — needs a storage decision first: Supabase Storage (recommended, persistent across Render deploys) vs local `public` disk (ephemeral on Render; self-heals at the next daily sync). Then set `THEMEALDB_MIRROR_IMAGES=true` + disk env vars on Render — the first prod sync downloads everything at once (~793 images; pooled, should still fit the 30s cron timeout — verify the first cron run's stats).
2. **About-page attribution** — owner will add a dedicated About page mentioning TheMealDB (data + artwork credit, required by terms for paid keys); no footer credit.
3. **Area normalization** (optional) — ~190 TheMealDB submissions have empty/uncanonical areas (`United States`, `France` vs `American`, `French`); map them during sync if the cuisines page gets noisy.
4. **Cheap freshness** (optional) — premium V2 `latest.php` (10 newest meals) could replace full letter enumeration for daily checks.
