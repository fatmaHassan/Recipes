import { Locator, Page } from '@playwright/test';

export class HomePage {
  readonly url = '/';
  readonly page: Page;
  readonly heading: Locator;
  readonly navigation: Locator;
  readonly loginLink: Locator;
  readonly getStartedLink: Locator;
  readonly registerLink: Locator;

  constructor(page: Page) {
    this.page = page;
    this.heading = page.locator('h1');
    this.navigation = page.locator('nav');
    this.loginLink = page.getByRole('link', { name: /log in|login/i });
    this.getStartedLink = page.getByRole('link', { name: /get started/i });
    this.registerLink = page.getByRole('link', { name: /register/i });
  }

  async goto() {
    await this.page.goto(this.url);
  }
}
