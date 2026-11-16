import { test as base } from '@playwright/test';

/**
 * The seeded E2E user, created by DatabaseSeeder into the `recipes_e2e`
 * database (wiped + reseeded on every `npm run test:e2e` run).
 *
 * Single source of truth — never hardcode these credentials in specs.
 * Override locally via E2E_EMAIL / E2E_PASSWORD if needed.
 */
export const E2E_USER = {
  name: 'Test User',
  email: process.env.E2E_EMAIL ?? 'test@example.com',
  password: process.env.E2E_PASSWORD ?? 'password',
};

/**
 * Generate a unique email for registration tests.
 *
 * Tests run fully parallel and share one seeded database, so every
 * registration must use a fresh address to avoid "email already
 * taken" collisions between workers and across re-runs.
 */
export function uniqueEmail(prefix = 'e2e') {
  return `${prefix}+${Date.now()}-${Math.random().toString(36).slice(2, 8)}@example.com`;
}

/**
 * Login a user. Success redirects to /my-recipes, so we wait for that
 * URL specifically (a generic / pattern matches every URL and would
 * resolve immediately without actually waiting for the redirect).
 */
export async function login(page, email = E2E_USER.email, password = E2E_USER.password) {
  await page.goto('/login');

  await page.waitForSelector('input[name="email"]', { state: 'visible' });

  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);

  await page.getByRole('button', { name: /log in/i }).click();

  await page.waitForURL('**/my-recipes', { timeout: 10000 });
  await page.waitForLoadState('domcontentloaded');
}

/**
 * Register a new user. Success redirects to /dashboard.
 * Defaults to a unique email — pass your own only if you have a reason.
 */
export async function register(page, { name = 'New User', email = uniqueEmail(), password = 'password' } = {}) {
  await page.goto('/register');

  await page.fill('input[name="name"]', name);
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.fill('input[name="password_confirmation"]', password);

  await page.getByRole('button', { name: /register/i }).click();

  await page.waitForURL('**/dashboard', { timeout: 10000 });

  return { name, email, password };
}

/**
 * Extend the base test with an authenticated page fixture.
 * Reuses the shared login() helper instead of duplicating it.
 */
export const test = base.extend({
  authenticatedPage: async ({ page }, use) => {
    await login(page);
    await use(page);
  },
});
