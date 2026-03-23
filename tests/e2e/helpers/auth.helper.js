import { test as base } from '@playwright/test';

/**
 * Extend the base test with authentication helpers
 */
export const test = base.extend({
  // Authenticated page fixture
  authenticatedPage: async ({ page, baseURL }, use) => {
    // Login before using the page
    await page.goto('/login');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    
    // Wait for navigation after login
    await page.waitForURL(/\/dashboard|\//);
    
    await use(page);
  },
});

/**
 * Helper function to login a user
 */
export async function login(page, email = 'test@example.com', password = 'password') {
  await page.goto('/login');
  
  // Wait for the login form to be ready
  await page.waitForSelector('input[name="email"]', { state: 'visible' });
  
  // Fill in the form
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  
  // Click the login button (using getByRole for better reliability)
  await page.getByRole('button', { name: /log in/i }).click();
  
  // Wait for navigation - could go to dashboard or home
  await page.waitForURL(/\/dashboard|\//, { timeout: 10000 });
  
  // Wait for page to be ready (use domcontentloaded instead of networkidle for better reliability)
  await page.waitForLoadState('domcontentloaded');
}

/**
 * Helper function to register a new user
 */
export async function register(page, name = 'Test User', email = 'test@example.com', password = 'password') {
  await page.goto('/register');
  await page.fill('input[name="name"]', name);
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.fill('input[name="password_confirmation"]', password);
  await page.click('button[type="submit"]');
  await page.waitForURL(/\/dashboard|\//);
}
