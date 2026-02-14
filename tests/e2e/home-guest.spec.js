import { test, expect } from '@playwright/test';

test.describe('Home Page -Guest View', {
  tag: '@smoke',
}, () => {
  test('should load the home page successfully', async ({ page }) => {
    // Navigate to the home page
    await page.goto('/');

    // Check that the page loaded successfully
    await expect(page).toHaveURL(/\/$/);

    // Verify page content for guest users
    await expect(page.locator('h1')).toContainText(/Fastest Way to Find|Welcome back/i);
  });

  test('@smoke should display navigation elements', async ({ page }) => {
    await page.goto('/');

    // Check for navigation - it should be visible
    const navigation = page.locator('nav');
    await expect(navigation).toBeVisible();

    // Check for login/register links for guest users
    const loginLink = page.getByRole('link', { name: /log in|login/i });
    await expect(loginLink).toBeVisible();
  });

  test('@smoke should have working login link', async ({ page }) => {
    await page.goto('/');
    
    // Check that login link exists and visible
    await expect(page.getByRole('link', { name: /log in|login/i })).toBeVisible();
    
    
  });

  test('@smoke should have working register link', async ({ page }) => {
    await page.goto('/');
    
    // register link exists and visible
    await expect(page.getByRole('link',{name: /get started/i})).toBeVisible();
    await expect(page.getByRole('link',{name: /register/i})).toBeVisible();
  });
});
