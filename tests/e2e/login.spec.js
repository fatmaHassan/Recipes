import { test, expect } from '@playwright/test';
import { LoginPage } from './pages/login.page';
import { E2E_USER } from './helpers/auth.helper.js';

test.describe('Login', {
  tag: '@smoke',
}, () => {
  let loginPage;
  test.beforeEach(async ({ page }) => {
    loginPage = new LoginPage(page);
       await loginPage.goto();
  });
  test('Should have correct title', async ({ page }) => {
    await loginPage.goto();
    await expect(page).toHaveTitle('Log In — Recipes');
  });

  test('login fields should be visible', async ({ page }) => {
    await loginPage.goto();
    await expect(loginPage.emailInput).toBeVisible();
    await expect(loginPage.passwordInput).toBeVisible();
    await expect(loginPage.submitButton).toBeVisible();
  });

  test('logs in with the seeded user and lands on My Recipes', async ({ page }) => {
    await loginPage.login(E2E_USER.email, E2E_USER.password);

   await page.waitForURL('**/my-recipes');
   await expect(page).toHaveURL(/.*my-recipes/);
  });

  test('shows an error for wrong credentials', async ({ page }) => {
    await loginPage.login(E2E_USER.email, 'wrong-password');

   await expect(loginPage.errorMessage).toContainText('These credentials do not match our records.');
  });

  test('logs out and protects /my-recipes again', async ({ page }) => {
    await loginPage.login(E2E_USER.email, E2E_USER.password);
    await page.waitForURL('**/my-recipes');

    // "Log Out" lives inside the user dropdown in the desktop navigation
    await page.getByRole('button', { name: new RegExp(E2E_USER.name, 'i') }).click();
    await page.getByRole('link', { name: /log out/i }).click();

    // ensure the user is redirected to the home page after logging out
    await expect(page).toHaveURL('/');
    
   // ensure the user is redirected to the login page when trying to access /my-recipes after logging out
    await page.goto('/my-recipes');
    await expect(page).toHaveURL(/.*login/);

  });
});