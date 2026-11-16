# Playwright E2E Tests

End-to-end tests using Playwright against a dedicated, disposable
`recipes_e2e` database. The dev `recipes` database is never touched.

## First-time setup (once)

```bash
cp .env.e2e.example .env.e2e      # adjust DB_* for your machine; file is gitignored
php artisan key:generate --env=e2e
createdb recipes_e2e              # skip if it already exists
npm run test:e2e:setup            # migrate + seed
```

The seeder creates the shared test user (defaults shown; configurable via
`E2E_EMAIL` / `E2E_PASSWORD` in `.env.e2e`):
- Email: `test@example.com`
- Password: `password`
- Email verified: yes

## Running tests

```bash
npm run test:e2e                  # 1. re-seeds recipes_e2e  2. runs ALL tests
npm run test:e2e:ui               # seeds + interactive UI mode
npm run test:e2e:headed           # seeds + visible browser
npm run test:e2e:debug            # seeds + Playwright inspector
```

Every `npm run test:e2e:*` script re-seeds first (`migrate:fresh --seed
--env=e2e`). Intentionally destructive — but only for `recipes_e2e`.
Never run it without `--env=e2e` when dev data matters.

### Faster targeted runs (assumes DB is already seeded)

```bash
npx playwright test                                             # all tests, no re-seed
npx playwright test tests/e2e/login.spec.js                     # one file
npx playwright test tests/e2e/login.spec.js --grep "wrong"      # tests matching a title
npx playwright test --grep @smoke                               # PR smoke suite
npx playwright test --project=chromium                          # chromium | firefox | webkit
```

Raw `npx playwright test` does **not** re-seed. It still auto-starts the
Laravel server (`php artisan serve --env=e2e`, reuses a running one
outside CI).

## Test structure

- `home-guest.spec.js` — public home page
- `login.spec.js`, `register.spec.js` — auth flows (rendering + functional)
- `dashboard.spec.js` — authenticated dashboard (uses the login helper)
- `custom-recipes.spec.js` — custom recipes CRUD
- `pages/` — Page Object Model classes (one per page under test)
- `helpers/auth.helper.js` — seeded credentials (`E2E_USER`), `login()`,
  `register()`, `uniqueEmail()`

## Authentication helpers

```javascript
// Login as the seeded user — no hardcoded credentials needed
await login(page);

// Register a fresh user — unique email by default (parallel-safe)
await register(page, { name: 'New User' });
```

Registration tests must never use the shared seeded email; rely on
`uniqueEmail()` from the helper.

## Configuration & CI

`playwright.config.js`: base URL `http://localhost:8000`, auto-starts the
server, runs Chromium/Firefox/WebKit, captures screenshot + video + trace
on failure. CI: chromium only, `@smoke` on PRs, full suite on pushes,
retries ×2, 1 worker, HTML report.
