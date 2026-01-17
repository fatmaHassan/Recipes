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

### Cypress Tests (requires Node.js 20+)
```bash
npm run cypress:open
```

## License

This project is open-sourced software.
