import { test, expect } from '@playwright/test';
import { LoginPage } from './pages/login.page';

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
});