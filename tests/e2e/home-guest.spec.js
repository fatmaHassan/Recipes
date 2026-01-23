import { test, expect } from '@playwright/test';

test.describe('Home Page', () => {
  test('should load the home page successfully', async ({ page }) => {
    // Navigate to the home page
    await page.goto('/');

    // Check that the page loaded successfully
    await expect(page).toHaveURL(/\/$/);

    // Verify page content for guest users
    await expect(page.locator('h1')).toContainText(/Fastest Way to Find|Welcome back/i);
  });

  test('should display navigation elements', async ({ page }) => {
    await page.goto('/');

    // Check for navigation - it should be visible
    const navigation = page.locator('nav');
    await expect(navigation).toBeVisible();

    // Check for login/register links for guest users
    const loginLink = page.getByRole('link', { name: /log in|login/i });
    await expect(loginLink).toBeVisible();
  });

  test('should have working login link', async ({ page }) => {
    await page.goto('/');
    
    // Click on login link
    await page.getByRole('link', { name: /log in|login/i }).click();
    
    // Should navigate to login page
    await expect(page).toHaveURL(/\/login/);
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
  });

  test('should have working register link', async ({ page }) => {
    await page.goto('/');
    
    // Click on register link
    await page.getByRole('link', { name: /register|get started/i }).first().click();
    
    // Should navigate to register page
    await expect(page).toHaveURL(/\/register/);
    await expect(page.locator('input[name="name"]')).toBeVisible();
    await expect(page.locator('input[name="email"]')).toBeVisible();
  });
});
