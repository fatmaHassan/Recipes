import { test, expect } from '@playwright/test';
import { RegisterPage } from './pages/register.page';
import { E2E_USER, uniqueEmail } from './helpers/auth.helper.js';

test.describe('Register',{
  tag: '@smoke',
}, () => {

  let registerPage;

  test.beforeEach(async ({ page }) => {
    registerPage = new RegisterPage(page);
  });

  test('Should have correct title', async({ page }) => {
    await page.goto('/register');
    await expect(page).toHaveTitle('Create Account — Recipes');
      });

  test('register fields should be visible', async ({ page }) => {
    await page.goto('/register');
    await expect(page.locator('input[name="name"]')).toBeVisible();
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('input[name="password_confirmation"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('registers a new user and lands on the dashboard', async ({ page }) => {
    // Unique email: parallel workers share one seeded database
    const email = uniqueEmail();

    await registerPage.goto();
    await registerPage.submit('New User', email, 'password');

// assert the successful-register redirect target. The controller sends you to /dashboard — wait for it, then check the URL.
    await page.waitForURL('**/dashboard');
    await expect(page.getByRole('button', { name: /New User/ })).toBeVisible();

  });

  test('rejects an email that is already taken', async ({ page }) => {
    // Try to register with the seeded user's address
    await registerPage.goto();
    await registerPage.submit('Duplicate', E2E_USER.email, 'password');
// assert the duplicate-email validation message. Read resources/views/auth/register.blade.php + RegisterRequest to find the text — then use registerPage.errorFor('email') with toContainText (or getByText).
    await expect(registerPage.errorFor('email')).toContainText('The email has already been taken.');
  });

  test('rejects mismatched password confirmation', async ({ page }) => {
    await registerPage.goto();
    // 4th argument fills password_confirmation with something different
    await registerPage.submit('Mismatch', uniqueEmail(), 'password', 'different-password');

    // ✍️ YOUR TURN: assert the confirmation-mismatch validation message.
    // hint: registerPage.errorFor('password_confirmation')
    await expect(registerPage.errorFor('password_confirmation')).toContainText('The password field confirmation does not match');
  });
});
