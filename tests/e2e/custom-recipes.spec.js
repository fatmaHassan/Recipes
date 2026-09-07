import { test, expect } from '@playwright/test';
import { login } from './helpers/auth.helper.js';
import { CustomRecipesPage } from './pages/custom-recipes.page';


test.describe('CustomRecipes',  () => {
  let customRecipesPage;

  test.beforeEach(async ({ page }) => {
     await login(page);
    customRecipesPage = new CustomRecipesPage(page);
    await customRecipesPage.goto();
    await customRecipesPage.waitUntilReady();
  });

  test('Should have correct title', async({ page }) => {
    await expect(page).toHaveTitle('My custom recipes — Recipes');
  });


  test('should display add custom recipe button @smoke', async ({ page }) => {

 // check that add custom recipe is visible
       await expect(customRecipesPage.addCutomRecipeLink).toBeVisible();

  });
});
