# Test Cases Documentation - Recipes Application

This document provides an index of all test cases organized by feature area. Each feature area has its own detailed test cases file.

## Quick Links

1. [Authentication & Authorization](test-cases/01-authentication.md)
2. [Recipe Management](test-cases/02-recipes.md)
3. [Ingredient Management](test-cases/03-ingredients.md)
4. [User Profile](test-cases/04-profile.md)
5. [Favorites](test-cases/05-favorites.md)
6. [Navigation & UI](test-cases/06-navigation-ui.md)
7. [Unit Tests](test-cases/07-unit-tests.md)

---

## Test Statistics

### Summary
- **Total Test Cases**: 25+
- **Feature Tests**: 15+
- **E2E Tests (Playwright)**: 6
- **E2E Tests (Cypress)**: 3
- **Unit Tests**: 1

### Coverage by Priority
- **P0 (Critical)**: 10+ test cases
- **P1 (High)**: 10+ test cases
- **P2 (Medium)**: 1+ test cases
- **P3 (Low)**: 0 test cases

### Status Legend
- ✅ Implemented
- ⚠️ Needs Review
- ❌ Not Implemented
- 🔄 In Progress

---

## Test Case Template

For future test cases, use this template:

```markdown
#### TC-XXX-XXX: Test Case Name
- **Type**: [Feature Test | E2E Test | Unit Test]
- **File**: `path/to/test/file.php::test_method_name`
- **Priority**: [P0 | P1 | P2 | P3]
- **Description**: Brief description of what is being tested
- **Preconditions**: Any setup required before test execution
- **Steps**:
  1. Step 1
  2. Step 2
- **Expected Result**: 
  - Expected outcome 1
  - Expected outcome 2
- **Status**: [✅ Implemented | ⚠️ Needs Review | ❌ Not Implemented]
```

---

## Related Documentation

- [Test Plan](TEST_PLAN.md) - Comprehensive test strategy and execution plan
- [E2E Testing Guide](../../tests/e2e/README.md) - Guide for running Playwright E2E tests

---

**Document Version**: 2.0  
**Last Updated**: January 2026  
**Maintained By**: Development Team
