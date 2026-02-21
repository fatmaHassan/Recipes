import { test, expect } from '@playwright/test';

test.describe('Home Page -Guest View',  () => {

  test.beforeEach(async ({ page }) => {
// navigate to home page before each test
    await page.goto('/');
  });


  test('Should have correct title', async({ page }) => {
await expect(page).toHaveTitle('Home — Recipes');
  });

  test('should load the home page successfully', async ({ page }) => {

    // Check that the page loaded successfully
    await expect(page).toHaveURL(/\/$/);

    // Verify page content for guest users
    await expect(page.locator('h1')).toContainText(/Fastest Way to Find|Welcome back/i);
  });

  test('@smoke should display navigation elements', async ({ page }) => {

    // Check for navigation - it should be visible
    const navigation = page.locator('nav');
    await expect(navigation).toBeVisible();

  });

  test('@smoke should have working login link', async ({ page }) => {    
    // Check that login link exists and visible
    await expect(page.getByRole('link', { name: /log in|login/i })).toBeVisible();
    
    
  });

  test('@smoke should have working register link', async ({ page }) => {    
    // register link exists and visible
    await expect(page.getByRole('link',{name: /get started/i})).toBeVisible();
    await expect(page.getByRole('link',{name: /register/i})).toBeVisible();
  });
});
