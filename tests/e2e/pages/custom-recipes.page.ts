import { Locator, Page } from '@playwright/test';

export class CustomRecipesPage {
  readonly url = '/custom-recipes';
  readonly page: Page;
  readonly addCutomRecipeLink: Locator;
  

  constructor(page: Page) {
    this.page = page;
   
this.addCutomRecipeLink = page.getByRole('link', { name: 'Add custom recipe' });
  
  }
  async goto() {
    await this.page.goto(this.url);
  }

  async waitUntilReady() {
    await this.page.waitForLoadState('networkidle');
  }
}