# Playwright E2E Tests

This directory contains end-to-end tests using Playwright.

## Running Tests

### Run all tests
```bash
npm run test:e2e
```

The E2E scripts reset and seed the dedicated `recipes_e2e` database before
running. They do not use the development `recipes` database.

### Run tests in UI mode (interactive)
```bash
npm run test:e2e:ui
```

### Run tests in headed mode (see browser)
```bash
npm run test:e2e:headed
```

### Run tests in debug mode
```bash
npm run test:e2e:debug
```

### Run specific test file
```bash
npx playwright test tests/e2e/example.spec.js
```

### Run tests in specific browser
```bash
npx playwright test --project=chromium
npx playwright test --project=firefox
npx playwright test --project=webkit
```

## Test Structure

- `example.spec.js` - Basic home page tests
- `dashboard.spec.js` - Dashboard tests (requires authentication)
- `helpers/auth.helper.js` - Authentication helper functions

## Authentication Helpers

Use the authentication helpers for tests that require login:

```javascript
import { login, register } from './helpers/auth.helper.js';

test('my test', async ({ page }) => {
  await login(page, 'test@example.com', 'password');
  // Your test code here
});
```

Or use the authenticated page fixture:

```javascript
import { test } from './helpers/auth.helper.js';

test('my test', async ({ authenticatedPage }) => {
  // authenticatedPage is already logged in
  await authenticatedPage.goto('/dashboard');
});
```

## Setup

The Playwright suite uses a separate database named `recipes_e2e`. Create a
local E2E environment file once:

```bash
cp .env.e2e.example .env.e2e
php artisan key:generate --env=e2e
```

Adjust the PostgreSQL username, password, host, and port in `.env.e2e` if
they differ on your machine. Then create the database if it does not exist:

```bash
createdb recipes_e2e
```

Before each Playwright run, the test command runs:

```bash
php artisan migrate:fresh --seed --env=e2e
```

This command is intentionally destructive, but it targets only `recipes_e2e`.
Never run it without `--env=e2e` when you need to preserve development data.

This will create a test user with:
- Email: `test@example.com`
- Password: `password`
- Email verified: Yes

The seeded user is shared by authenticated page tests. Registration tests
should use a unique email address instead of this shared fixture.

## Configuration

Tests are configured in `playwright.config.js`:
- Base URL: `http://localhost:8000`
- Automatically starts Laravel server before tests
- Tests run in Chromium, Firefox, and WebKit browsers
- Screenshots and videos captured on failure

## Writing Tests

### Basic Test Example

```javascript
import { test, expect } from '@playwright/test';

test('should do something', async ({ page }) => {
  await page.goto('/');
  await expect(page.locator('h1')).toBeVisible();
});
```

### Using Selectors

Playwright recommends using semantic selectors:

```javascript
// Good - semantic selectors
await page.getByRole('button', { name: 'Submit' }).click();
await page.getByLabel('Email').fill('test@example.com');
await page.getByText('Welcome').isVisible();

// Also works - CSS selectors
await page.locator('button[type="submit"]').click();
```

## Debugging

1. Use `npm run test:e2e:debug` to run tests in debug mode
2. Use `page.pause()` in your test to pause execution
3. Use Playwright Inspector: `PWDEBUG=1 npm run test:e2e`
4. Check screenshots and videos in `test-results/` directory

## CI/CD

Tests are configured to:
- Retry failed tests 2 times in CI
- Run with 1 worker in CI (sequential)
- Generate HTML reports
