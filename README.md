# Recipes Application

A Laravel application that helps you discover recipes based on ingredients you have at home, with allergy filtering.

## Features

- Manage your home ingredients
- Add and manage allergies
- Search recipes from TheMealDB API
- Filter recipes by allergies
- Save favorite recipes
- Beautiful, modern UI with Tailwind CSS

## Requirements

- PHP 8.2+
- Composer
- Node.js 20+ (for building assets) or Node.js 14+ (with CDN fallback)
- MySQL/PostgreSQL or SQLite

## Installation

1. Clone the repository
2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node dependencies:
   ```bash
   npm install
   ```

4. Copy environment file:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Run migrations:
   ```bash
   php artisan migrate
   ```

7. Build assets (if Node.js 20+):
   ```bash
   npm run build
   ```
   
   If you have Node.js < 20, the postinstall script will create a minimal manifest and the app will use CDN fallbacks.

8. Start the development server:
   ```bash
   php artisan serve
   ```

## Asset Building

The application includes a smart build script (`scripts/build-assets.js`) that:
- Attempts to build assets with Vite if Node.js 20+ is available
- Creates a minimal manifest if the build fails (Node.js < 20)
- Automatically runs on `npm install` via the `postinstall` script

For production deployments, ensure Node.js 20+ is available for proper asset building.

## Testing

### PHPUnit Tests
```bash
php artisan test
```
or
```bash
composer test
```

### Playwright E2E Tests
```bash
npm run test:e2e
```

For more Playwright testing options, see [tests/e2e/README.md](tests/e2e/README.md).

### Cypress Tests (requires Node.js 20+)
```bash
npm run cypress:open
```

## CI/CD

This project includes GitHub Actions workflows that automatically run tests on push and pull requests:

- **PHPUnit Tests**: Runs all PHP unit and feature tests
- **Playwright E2E Tests**: Runs end-to-end tests in Chromium browser

The workflow is configured in `.github/workflows/tests.yml` and will:
1. Set up PHP 8.2 and Node.js 20
2. Install dependencies
3. Set up the database and run migrations
4. Run PHPUnit tests
5. Run Playwright E2E tests
6. Upload test reports and artifacts on failure

Test artifacts (screenshots, videos, HTML reports) are automatically uploaded when tests fail and are available for 30 days.

## License

This project is open-sourced software.
