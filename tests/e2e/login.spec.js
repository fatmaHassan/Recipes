import { test, expect } from '@playwright/test';
test.describe('Login', () => {


  test('Should have correct title', async({ page }) => {
    await page.goto('/login');
    await expect(page).toHaveTitle('Log In — Recipes');
      });

  test('login fields should be visible', async ({ page }) => {
    await page.goto('/login');
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
    await expect(page.locator('a[href*="forgot-password"]')).toBeVisible();
    await expect(page.getByRole('link', { name: /forgot your password/i })).toBeVisible();
  });
});