import { Locator, Page } from '@playwright/test';

export class LoginPage {
  readonly url = '/login';
  readonly page: Page;
  readonly emailInput: Locator;
  readonly passwordInput: Locator;
  readonly submitButton: Locator;
  /**
   * Red validation list rendered by <x-input-error>.
   * On a failed login the message is
   * "These credentials do not match our records."
   */
  readonly errorMessage: Locator;

  constructor(page: Page) {
    this.page = page;
    this.emailInput = page.locator('input[name="email"]');
    this.passwordInput = page.locator('input[name="password"]');
    this.submitButton = page.getByRole('button', { name: /log in/i });
    this.errorMessage = page.locator('ul.text-red-600');
  }

  async goto() {
    await this.page.goto(this.url);
  }

  /**
   * Fill the login form and submit. On success the app redirects
   * to /my-recipes — wait for it in the spec with page.waitForURL.
   */
  async login(email: string, password: string) {
    await this.emailInput.fill(email);
    await this.passwordInput.fill(password);
    await this.submitButton.click();
  }
}
