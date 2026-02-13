# Recipes API Postman Collection

This directory contains Postman collections and environments for testing the Recipes application Laravel API endpoints.

## 📁 Files

- **Recipes-API-Collection.json** - Complete Postman collection with all API endpoints, tests, and assertions
- **Recipes-Environment.json** - Postman environment file with variables
- **README.md** - This file

## 🚀 Quick Start

### 1. Import into Postman

1. Open Postman Desktop or Web App
2. Click **Import** button (top left)
3. Select both JSON files:
   - `Recipes-API-Collection.json`
   - `Recipes-Environment.json`
4. Click **Import**

### 2. Set Up Environment

1. In Postman, click the **Environments** icon (left sidebar)
2. Select **Recipes API - Production**
3. Verify variables are set correctly:
   - `base_url`: `http://localhost:0000/api`
   - Set `auth_token`, `recipes_website_user_mail`, `recipes_website_user_password` for authenticated requests

### 3. Run Requests

1. Select the **Recipes API Collection**
2. Select the **Recipes API - Production** environment (top right dropdown)
3. Click on any request to view details
4. Click **Send** to execute
5. View **Test Results** tab to see automated test results

## 📋 Collection Structure

All endpoints are served by the Laravel API at `{{base_url}}` (e.g. `http://localhost:0000/api`).

### Organisation: OpenAPI import vs tag-based folders

- **OpenAPI import** often creates one folder per operation (e.g. `login`, `logout`, `register` as top-level folders). That matches the spec but can be flat and harder to navigate.
- **Tag-based / domain folders** (e.g. **Authentication** with login, register, logout inside; **Recipes**, **Ingredients**) are the recommended approach:
  - Group by feature/domain (how you think about the API).
  - Easier to find requests and to run subsets (e.g. “all auth” or “all recipes”).
  - Aligns with OpenAPI `tags` if your spec uses them.

Use the **tag-based structure** (Authentication, Recipes, Ingredients, etc.) as the main organisation; treat the raw OpenAPI import as a starting point and reorganise into these folders.

### Where to put negative and scenario tests


## ✅ Automated Tests

Each request includes automated tests that verify:

- ✅ Status code is 200
- ✅ Response time < 2000ms
- ✅ Response is valid JSON
- ✅ Response structure matches expected format
- ✅ Required fields are present
- ✅ Data types are correct

## 🔧 Collection Variables

The collection uses these variables (can be overridden per request):

| Variable | Default Value | Description |
|----------|--------------|-------------|
| `base_url` | `http://localhost:0000/api` | Base URL for Laravel API |
| `auth_token` | (set from login) | Bearer token for authenticated requests |
| `recipes_website_user_mail` | — | User email for login |
| `recipes_website_user_password` | — | User password for login |
| `user_register_email` | — | Email for registration |
| `user_register_password` | — | Password for registration |
| `user_register_name` | — | Name for registration |

## 🔐 Run order and auth token

When you run **Login → Register → Logout** in that order:

1. **Login** sets `auth_token` in the environment (from the response).
2. **Register** does not set or change `auth_token` (Laravel register returns 201; you use Login to get a token).
3. **Logout** invalidates the token on the server. If the Logout request’s test script calls `pm.environment.unset("auth_token")`, the token is also removed from the environment, so any later request that needs `{{auth_token}}` will fail.

**How to keep a valid token for the rest of the collection:**

- **Run Logout last (recommended):** Use run order **Register → Login → (all other requests: Recipes, Ingredients, etc.) → Logout**. That way the token stays valid for every authenticated request and is only invalidated at the end.
- **Do not clear the token in Logout:** In the Logout request’s **Tests** tab, avoid `pm.environment.unset("auth_token")`. The variable then stays set in Postman (the token is still invalid on the server after logout). Useful if you run requests manually after Logout and don’t want the variable to disappear.
- **Re-login after Logout:** If you need to run Logout in the middle and then more authenticated requests, add a second **Login** request (e.g. “Login (re-auth)”) after Logout so the collection gets a fresh token for the following requests.

## 🧪 Running Tests

### Manual Testing in Postman

1. Select a request
2. Click **Send**
3. Check the **Test Results** tab for pass/fail status

### Run Collection (All Requests)

1. Click on **Recipes API Collection** (three dots menu)
2. Select **Run collection**
3. Click **Run Recipes API Collection**
4. View results for all requests

### Using Newman (CLI)

Newman is Postman's command-line collection runner. Perfect for CI/CD!

#### Installation

```bash
npm install -g newman
```

#### Run Collection

```bash
# Basic run
newman run postman/Recipes-API-Collection.json -e postman/Recipes-Environment.json

# With HTML report
newman run postman/Recipes-API-Collection.json \
  -e postman/Recipes-Environment.json \
  --reporters cli,html \
  --reporter-html-export postman/reports/newman-report.html

# With detailed output
newman run postman/Recipes-API-Collection.json \
  -e postman/Recipes-Environment.json \
  --verbose
```

## 🔄 CI/CD Integration

### GitHub Actions Example

Add this to `.github/workflows/postman-tests.yml`:

```yaml
name: Postman API Tests

on:
  schedule:
    - cron: '0 2 * * *'  # Run daily at 2 AM
  workflow_dispatch:  # Manual trigger

jobs:
  api-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
      
      - name: Install Newman
        run: npm install -g newman
      
      - name: Run Postman Collection
        run: |
          newman run postman/Recipes-API-Collection.json \
            -e postman/Recipes-Environment.json \
            --reporters cli,junit \
            --reporter-junit-export postman/reports/results.xml
      
      - name: Upload Test Results
        if: always()
        uses: actions/upload-artifact@v3
        with:
          name: postman-results
          path: postman/reports/
```

## 📝 Customizing Tests

### Adding New Test Cases

1. Open Postman
2. Select a request
3. Go to **Tests** tab
4. Add your test script:

```javascript
pm.test("Your test name", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.someProperty).to.equal("expectedValue");
});
```

### Modifying Variables

Variables can be set at three levels (priority order):
1. **Environment** - Applies to all requests using that environment
2. **Collection** - Applies to all requests in the collection
3. **Request** - Applies only to that specific request

## 🐛 Troubleshooting

### Tests Failing

- Ensure the Laravel API server is running (e.g. `php artisan serve` or your chosen host/port)
- Verify `base_url` is correct (e.g. `http://localhost:0000/api`)
- Verify environment variables are set correctly (including `auth_token` for protected routes)
- Check network connectivity and CORS if calling from another origin

### Variables Not Working

- Ensure environment is selected (top right dropdown)
- Check variable names match exactly (case-sensitive)
- Verify variable scope (environment vs collection vs request)

### Rate Limiting

If the Laravel API uses rate limiting:
- Add delays between requests in Newman: `--delay-request 1000`
- Run tests less frequently or adjust rate limit config in the app

## 📚 Resources

- [Postman Documentation](https://learning.postman.com/docs/)
- [Newman Documentation](https://learning.postman.com/docs/running-collections/using-newman-cli/command-line-integration-with-newman/)
- [Laravel API Documentation](https://laravel.com/docs/api-resources)
- [Postman Test Scripts](https://learning.postman.com/docs/writing-scripts/test-scripts/)

## 🔗 Related Files

- `routes/api.php` - Laravel API route definitions
- `app/Http/Controllers/Api/` - API controllers
- `tests/Feature/` - PHPUnit API tests

## 📄 License

Same as the main project.
