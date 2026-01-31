# Ingredient Management Test Cases

This document contains all test cases related to ingredient CRUD operations.

← [Back to Test Cases Index](../TEST_CASES.md)

---

## 3.1 Ingredient CRUD Operations

#### TC-ING-001: Create and Retrieve Ingredients
- **Type**: Feature Test
- **File**: `tests/Feature/IngredientApiTest.php::test_ingredient_creation_and_retrieval`
- **Priority**: P0 (Critical)
- **Description**: Verify ingredient creation and listing functionality
- **Preconditions**: User is authenticated
- **Steps**:
  1. POST to `/ingredients` with ingredient name "Chicken"
  2. GET `/ingredients`
- **Expected Result**: 
  - Ingredient created successfully
  - Redirected to ingredients index
  - Success message in session
  - Ingredient exists in database
  - Ingredients list page displays with ingredient data
- **Status**: ✅ Implemented

#### TC-ING-002: Delete Ingredient
- **Type**: Feature Test
- **File**: `tests/Feature/IngredientApiTest.php::test_ingredient_deletion`
- **Priority**: P1 (High)
- **Description**: Verify ingredient deletion functionality
- **Preconditions**: 
  - User is authenticated
  - Ingredient exists for the user
- **Steps**:
  1. DELETE `/ingredients/{id}`
- **Expected Result**: 
  - Ingredient deleted successfully
  - Redirected to ingredients index
  - Success message in session
  - Ingredient removed from database
- **Status**: ✅ Implemented

---

**Last Updated**: January 2026
