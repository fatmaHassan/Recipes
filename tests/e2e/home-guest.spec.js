import { test, expect } from '@playwright/test';
import { HomePage } from './pages/home.page';

test.describe('Home Page -Guest View',  () => {

  test.beforeEach(async ({ page }) => {
    const homePage = new HomePage(page);
    await homePage.goto();
  });


  test('Should have correct title', async({ page }) => {
await expect(page).toHaveTitle('Home — Recipes');
  });

  test('should load the home page successfully', async ({ page }) => {
    const homePage = new HomePage(page);

    // Check that the page loaded successfully
    await expect(page).toHaveURL(/\/$/);

    // Verify page content for guest users
    await expect(homePage.heading).toContainText(/Fastest Way to Find|Welcome back/i);
  });

  test('@smoke should display navigation elements', async ({ page }) => {
    const homePage = new HomePage(page);

    await expect(homePage.navigation).toBeVisible();

  });

  test('@smoke should have working login link', async ({ page }) => {
    const homePage = new HomePage(page);
    await expect(homePage.loginLink).toBeVisible();
  });

  test('@smoke should have working register link', async ({ page }) => {
    const homePage = new HomePage(page);
    await expect(homePage.getStartedLink).toBeVisible();
    await expect(homePage.registerLink).toBeVisible();
  });
});
