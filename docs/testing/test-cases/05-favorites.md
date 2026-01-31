# Favorites Test Cases

This document contains all test cases related to recipe favorites management.

← [Back to Test Cases Index](../TEST_CASES.md)

---

## 5.1 Favorites Management

#### TC-FAV-001: Add Recipe to Favorites (E2E)
- **Type**: E2E Test (Cypress)
- **File**: `cypress/e2e/favorites.cy.js::Authenticated user can add recipe to favorites`
- **Priority**: P1 (High)
- **Description**: Verify users can add recipes to favorites
- **Preconditions**: User is authenticated
- **Steps**:
  1. Navigate to recipe detail page (`/recipes/52772`)
  2. Click "Save Recipe"
  3. Click "Add to Favorites"
  4. Navigate to `/favorites`
- **Expected Result**: 
  - Recipe saved successfully
  - Recipe added to favorites
  - Favorites page displays "My Favorites"
  - Recipe appears in favorites list
- **Status**: ✅ Implemented

---

**Last Updated**: January 2026
