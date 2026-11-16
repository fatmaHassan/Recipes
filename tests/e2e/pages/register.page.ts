import { Locator, Page } from '@playwright/test';

export class RegisterPage {
  readonly url = '/register';
  readonly page: Page;
  readonly nameInput: Locator;
  readonly emailInput: Locator;
  readonly passwordInput: Locator;
  readonly passwordConfirmationInput: Locator;
  readonly submitButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.nameInput = page.locator('input[name="name"]');
    this.emailInput = page.locator('input[name="email"]');
    this.passwordInput = page.locator('input[name="password"]');
    this.passwordConfirmationInput = page.locator('input[name="password_confirmation"]');
    this.submitButton = page.getByRole('button', { name: /register/i });
  }

  async goto() {
    await this.page.goto(this.url);
  }

  /**
   * Fill every field and submit. On success the app redirects
   * to /dashboard — wait for it in the spec with page.waitForURL.
   * `passwordConfirmation` defaults to `password`; pass a different
   * value to trigger the confirmation-mismatch validation.
   */
  async submit(
    name: string,
    email: string,
    password: string,
    passwordConfirmation: string = password,
  ) {
    await this.nameInput.fill(name);
    await this.emailInput.fill(email);
    await this.passwordInput.fill(password);
    await this.passwordConfirmationInput.fill(passwordConfirmation);
    await this.submitButton.click();
  }

  /**
   * Red <x-input-error> list rendered directly under a given field.
   * e.g. errorFor('email') → "The email has already been taken."
   */
  errorFor(field: 'name' | 'email' | 'password' | 'password_confirmation'): Locator {
    return this.page.locator(`div:has(input[name="${field}"]) ul.text-red-600`);
  }
}
