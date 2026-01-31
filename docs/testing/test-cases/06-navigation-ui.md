# Navigation & UI Test Cases

This document contains all test cases related to navigation, user interface, and user experience.

← [Back to Test Cases Index](../TEST_CASES.md)

---

## 6.1 Home Page (Guest)

#### TC-UI-001: Home Page Loads Successfully
- **Type**: E2E Test (Playwright)
- **File**: `tests/e2e/home-guest.spec.js::should load the home page successfully`
- **Priority**: P1 (High)
- **Description**: Verify home page loads for guest users
- **Preconditions**: User is not authenticated
- **Steps**:
  1. Navigate to `/`
- **Expected Result**: 
  - Page loads successfully
  - URL matches `/`
  - Page contains heading with "Fastest Way to Find" or "Welcome back"
- **Status**: ✅ Implemented

#### TC-UI-002: Navigation Elements Display
- **Type**: E2E Test (Playwright)
- **File**: `tests/e2e/home-guest.spec.js::should display navigation elements`
- **Priority**: P1 (High)
- **Description**: Verify navigation is visible on home page
- **Preconditions**: User is not authenticated
- **Steps**:
  1. Navigate to `/`
- **Expected Result**: 
  - Navigation element is visible
  - Login link is visible
- **Status**: ✅ Implemented

#### TC-UI-003: Login Link Navigation
- **Type**: E2E Test (Playwright)
- **File**: `tests/e2e/home-guest.spec.js::should have working login link`
- **Priority**: P1 (High)
- **Description**: Verify login link navigates to login page
- **Preconditions**: User is not authenticated
- **Steps**:
  1. Navigate to `/`
  2. Click login link
- **Expected Result**: 
  - Navigated to `/login`
  - Email input field visible
  - Password input field visible
- **Status**: ✅ Implemented

#### TC-UI-004: Register Link Navigation
- **Type**: E2E Test (Playwright)
- **File**: `tests/e2e/home-guest.spec.js::should have working register link`
- **Priority**: P1 (High)
- **Description**: Verify register link navigates to registration page
- **Preconditions**: User is not authenticated
- **Steps**:
  1. Navigate to `/`
  2. Click register link
- **Expected Result**: 
  - Navigated to `/register`
  - Name input field visible
  - Email input field visible
- **Status**: ✅ Implemented

## 6.2 Dashboard (Authenticated)

#### TC-UI-005: Dashboard Display After Login
- **Type**: E2E Test (Playwright)
- **File**: `tests/e2e/dashboard.spec.js::should display dashboard after login`
- **Priority**: P0 (Critical)
- **Description**: Verify dashboard displays after successful login
- **Preconditions**: User is authenticated
- **Steps**:
  1. Login (via beforeEach)
  2. Navigate to `/dashboard`
- **Expected Result**: 
  - Dashboard page loads
  - Welcome message or dashboard content visible
  - OR redirected to home page (acceptable)
- **Status**: ✅ Implemented

#### TC-UI-006: Authenticated Navigation Links
- **Type**: E2E Test (Playwright)
- **File**: `tests/e2e/dashboard.spec.js::should display navigation links for authenticated users`
- **Priority**: P1 (High)
- **Description**: Verify authenticated users see appropriate navigation links
- **Preconditions**: User is authenticated
- **Steps**:
  1. Login (via beforeEach)
  2. Navigate to `/dashboard`
- **Expected Result**: 
  - Navigation element visible
  - At least one authenticated link visible (Ingredients or Dashboard)
- **Status**: ✅ Implemented

---

**Last Updated**: January 2026
