# Unit Tests Test Cases

This document contains all test cases related to unit testing of business logic and service layers.

← [Back to Test Cases Index](../TEST_CASES.md)

---

## 7.1 Recipe Service

#### TC-UNIT-001: Ingredient Search Returns Exact Matches First
- **Type**: Unit Test
- **File**: `tests/Unit/RecipeServiceTest.php::test_search_ingredients_returns_exact_matches_first`
- **Priority**: P1 (High)
- **Description**: Verify ingredient search prioritizes exact matches
- **Preconditions**: RecipeService with mocked getAllIngredients
- **Steps**:
  1. Call `searchIngredients('Chicken', 10)`
- **Expected Result**: 
  - Results are not empty
  - First result is exact match "Chicken"
  - All results contain "chicken" (case-insensitive)
- **Status**: ✅ Implemented

---

**Last Updated**: January 2026
