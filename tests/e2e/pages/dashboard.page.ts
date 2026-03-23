import { Locator, Page } from '@playwright/test';

export class DashboardPage {
  readonly url = '/dashboard';
  readonly page: Page;
  readonly navigation: Locator;
  readonly ingredientsLink: Locator;
  readonly dashboardLink: Locator;
  readonly welcomeMessage: Locator;

  constructor(page: Page) {
    this.page = page;
    this.navigation = page.locator('nav');
    this.ingredientsLink = this.navigation.getByRole('link', { name: /ingredients/i });
    this.dashboardLink = this.navigation.getByRole('link', { name: /dashboard/i });
    this.welcomeMessage = page.getByText(/Welcome back|Dashboard/i).first();
  }

  async goto() {
    await this.page.goto(this.url);
  }

  async waitUntilReady() {
    await this.page.waitForLoadState('networkidle');
  }
}
