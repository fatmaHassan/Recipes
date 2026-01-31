# Recipe Management Test Cases

This document contains all test cases related to recipe search, retrieval, and management.

← [Back to Test Cases Index](../TEST_CASES.md)

---

## 2.1 Recipe Search

#### TC-RECIPE-001: Search Recipes by Ingredients
- **Type**: Feature Test
- **File**: `tests/Feature/RecipeApiTest.php::test_recipe_search_by_ingredients_returns_results`
- **Priority**: P0 (Critical)
- **Description**: Verify recipe search returns results when searching by ingredients
- **Preconditions**: 
  - User is authenticated and email verified
  - TheMealDB API is mocked
- **Steps**:
  1. GET `/recipes/search` with ingredients parameter
- **Expected Result**: 
  - HTTP status 200
  - View `recipes.index` is rendered
  - `paginatedRecipes` data is available in view
- **Status**: ✅ Implemented

#### TC-RECIPE-002: Recipe Search Workflow (E2E)
- **Type**: E2E Test (Cypress)
- **File**: `cypress/e2e/recipe-search.cy.js::User can search recipes by selecting ingredients`
- **Priority**: P0 (Critical)
- **Description**: Complete workflow for searching recipes
- **Preconditions**: User is authenticated
- **Steps**:
  1. Navigate to `/ingredients`
  2. Add ingredient "Chicken"
  3. Navigate to `/dashboard`
  4. Select ingredient checkbox
  5. Click "Search Recipes"
- **Expected Result**: 
  - Redirected to `/recipes`
  - Recipe search results page displayed
  - "Recipe Search Results" text visible
- **Status**: ✅ Implemented

## 2.2 Recipe Details

#### TC-RECIPE-003: Recipe Details Endpoint
- **Type**: Feature Test
- **File**: `tests/Feature/RecipeApiTest.php::test_recipe_details_endpoint_returns_recipe`
- **Priority**: P0 (Critical)
- **Description**: Verify recipe details are retrieved correctly
- **Preconditions**: 
  - User is authenticated and email verified
  - TheMealDB API is mocked
- **Steps**:
  1. GET `/recipes/{id}` with valid recipe ID
- **Expected Result**: 
  - HTTP status 200
  - View `recipes.show` is rendered
  - `recipe` data is available in view
- **Status**: ✅ Implemented

---

**Last Updated**: January 2026
