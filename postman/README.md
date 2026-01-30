# Recipes API Postman Collection

This directory contains Postman collections and environments for testing TheMealDB API endpoints used in the Recipes application.

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
   - `base_url`: `https://www.themealdb.com/api/json/v1/1`
   - `ingredient`: `chicken` (default)
   - `recipe_id`: `52772` (default)

### 3. Run Requests

1. Select the **Recipes API Collection**
2. Select the **Recipes API - Production** environment (top right dropdown)
3. Click on any request to view details
4. Click **Send** to execute
5. View **Test Results** tab to see automated test results

## 📋 Collection Structure

### Recipes
- **Search Recipes by Ingredient** - `GET /filter.php?i={ingredient}`
- **Get Recipe Details by ID** - `GET /lookup.php?i={recipe_id}`

### Ingredients
- **Get All Ingredients List** - `GET /list.php?i=list`

### Test Scenarios
- **Search with Valid Ingredient** - Tests successful search
- **Search with Invalid Ingredient** - Tests null response handling
- **Get Recipe with Invalid ID** - Tests error handling

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
| `base_url` | `https://www.themealdb.com/api/json/v1/1` | Base URL for MealDB API |
| `ingredient` | `chicken` | Default ingredient for search |
| `recipe_id` | `52772` | Default recipe ID for lookup |
| `sample_meal_id` | (auto-set) | Automatically set from search results |
| `sample_meal_name` | (auto-set) | Automatically set from search results |
| `sample_ingredient` | (auto-set) | Automatically set from ingredients list |

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

- Check if MealDB API is accessible
- Verify environment variables are set correctly
- Check network connectivity
- Review response structure - API might have changed

### Variables Not Working

- Ensure environment is selected (top right dropdown)
- Check variable names match exactly (case-sensitive)
- Verify variable scope (environment vs collection vs request)

### Rate Limiting

TheMealDB API has rate limits. If you hit limits:
- Add delays between requests in Newman: `--delay-request 1000`
- Run tests less frequently
- Use collection runner with delays

## 📚 Resources

- [Postman Documentation](https://learning.postman.com/docs/)
- [Newman Documentation](https://learning.postman.com/docs/running-collections/using-newman-cli/command-line-integration-with-newman/)
- [TheMealDB API Documentation](https://www.themealdb.com/api.php)
- [Postman Test Scripts](https://learning.postman.com/docs/writing-scripts/test-scripts/)

## 🔗 Related Files

- `app/Services/RecipeService.php` - Service using these API endpoints
- `tests/Feature/RecipeApiTest.php` - PHPUnit tests with mocked responses

## 📄 License

Same as the main project.
