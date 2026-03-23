# 🍽️ Recipes Application

A Laravel application that helps users discover recipes based on available ingredients, with allergy filtering.

---

## 🧪 Testing (QA Focus)

This project demonstrates a **structured QA approach with multiple testing layers**:

* **PHPUnit** → unit & feature tests (backend logic)
* **Playwright** → end-to-end tests (real user workflows)
* **Postman API Tests** → API validation via collection (integrated in CI)

### Key QA Practices

* Page Object Model (POM) used in Playwright tests for maintainability
* Coverage of critical user flows (search, favorites, ingredients)
* API testing using Postman collections
* Validation of edge cases and error handling
* Automated testing via GitHub Actions (CI/CD)

---

## ✨ Features

* Manage your home ingredients
* Add and manage allergies
* Search recipes from TheMealDB API
* Filter recipes by allergies
* Save favorite recipes
* Modern UI with Tailwind CSS

---

## ⚙️ Requirements

* PHP 8.2+
* Composer
* Node.js 20+ (or 14+ with fallback)
* MySQL/PostgreSQL or SQLite

---

## 🚀 Installation

1. Clone the repository

2. Install dependencies:

   ```bash
   composer install
   npm install
   ```

3. Environment setup:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Run migrations:

   ```bash
   php artisan migrate
   ```

5. Build assets:

   ```bash
   npm run build
   ```

   (Node.js < 20 will use CDN fallback automatically)

6. Start server:

   ```bash
   php artisan serve
   ```

---

## 🧪 Running Tests

### PHPUnit

```bash
php artisan test
```

or

```bash
composer test
```

### Playwright (E2E)

```bash
npm run test:e2e
```

### API Tests (Postman Collection)

API tests are executed via a Postman collection (run in CI):

```bash
# Smoke tests
npx newman run postman/Recipes-Smoke-API-Collection.json -e postman/Recipes-Environment.json --env-var "base_url=http://127.0.0.1:8000/api"

# Regression tests
npx newman run postman/Recipes-Regression-API-Collection.json -e postman/Recipes-Environment.json --env-var "base_url=http://127.0.0.1:8000/api"
```

* Covers core endpoints and validation scenarios
* Integrated into GitHub Actions workflow

---

## 📄 Test Documentation

Detailed QA documentation:

* [Test Plan](docs/testing/TEST_PLAN.md)
* [Test Cases](docs/testing/TEST_CASES.md)

  * Authentication & Authorization
  * Recipe Management
  * Ingredient Management
  * User Profile
  * Favorites
  * Navigation & UI
  * Unit Tests

---

## 🔄 CI/CD

GitHub Actions workflow file:

* `.github/workflows/tests.yml`

This workflow runs automatically on every `push` and `pull_request` to `main`, `master`, and `develop`.

Jobs included in this workflow:

* `phpunit` → Runs Laravel unit and feature tests (`composer test`)
* `playwright` → Runs E2E tests (smoke suite on PRs, full suite on pushes)
* `api-tests` → Runs Newman API tests (smoke on PRs, regression on pushes)

Artifacts are uploaded from CI for debugging (for example Playwright reports and Newman reports).

---

## 🏗️ Asset Building

The application includes a smart build script (`scripts/build-assets.js`) that:

* Uses Vite when Node.js 20+ is available
* Falls back to a minimal manifest if not
* Runs automatically on `npm install`

---

## 📜 License

Open-source project.
