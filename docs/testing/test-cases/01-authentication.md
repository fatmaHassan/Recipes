# Authentication & Authorization Test Cases

This document contains all test cases related to user authentication and authorization.

← [Back to Test Cases Index](../TEST_CASES.md)

---

## 1.1 Login

#### TC-AUTH-001: Login Screen Rendering
- **Type**: Feature Test
- **File**: `tests/Feature/Auth/AuthenticationTest.php::test_login_screen_can_be_rendered`
- **Priority**: P0 (Critical)
- **Description**: Verify that the login page loads successfully
- **Preconditions**: User is not authenticated
- **Steps**:
  1. Navigate to `/login`
- **Expected Result**: 
  - HTTP status 200
  - Login form is visible
- **Status**: ✅ Implemented

#### TC-AUTH-002: Successful Login
- **Type**: Feature Test
- **File**: `tests/Feature/Auth/AuthenticationTest.php::test_users_can_authenticate_using_the_login_screen`
- **Priority**: P0 (Critical)
- **Description**: Verify users can login with valid credentials
- **Preconditions**: User exists in database
- **Steps**:
  1. POST to `/login` with valid email and password
- **Expected Result**: 
  - User is authenticated
  - Redirected to dashboard
- **Status**: ✅ Implemented

#### TC-AUTH-003: Login with Invalid Password
- **Type**: Feature Test
- **File**: `tests/Feature/Auth/AuthenticationTest.php::test_users_can_not_authenticate_with_invalid_password`
- **Priority**: P0 (Critical)
- **Description**: Verify login fails with incorrect password
- **Preconditions**: User exists in database
- **Steps**:
  1. POST to `/login` with valid email but wrong password
- **Expected Result**: 
  - User remains unauthenticated (guest)
  - Error message displayed
- **Status**: ✅ Implemented

#### TC-AUTH-004: Logout Functionality
- **Type**: Feature Test
- **File**: `tests/Feature/Auth/AuthenticationTest.php::test_users_can_logout`
- **Priority**: P0 (Critical)
- **Description**: Verify users can logout successfully
- **Preconditions**: User is authenticated
- **Steps**:
  1. POST to `/logout`
- **Expected Result**: 
  - User is logged out (guest)
  - Redirected to home page (`/`)
- **Status**: ✅ Implemented

#### TC-AUTH-005: Register and Login Flow (E2E)
- **Type**: E2E Test (Cypress)
- **File**: `cypress/e2e/authentication.cy.js::User can register and login`
- **Priority**: P0 (Critical)
- **Description**: Complete user registration and login workflow
- **Preconditions**: No existing user with test email
- **Steps**:
  1. Navigate to `/register`
  2. Fill registration form (name, email, password, password confirmation)
  3. Submit form
  4. Logout
  5. Navigate to `/login`
  6. Enter credentials
  7. Submit login form
- **Expected Result**: 
  - User successfully registered
  5. Redirected away from register page
  6. Successfully logged in
  7. User name visible on page
- **Status**: ✅ Implemented

## 1.2 Registration

#### TC-REG-001: Registration Test
- **Type**: Feature Test
- **File**: `tests/Feature/Auth/RegistrationTest.php`
- **Priority**: P0 (Critical)
- **Description**: Verify user registration functionality
- **Status**: ✅ Implemented (file exists, details not reviewed)

## 1.3 Email Verification

#### TC-VERIFY-001: Email Verification Test
- **Type**: Feature Test
- **File**: `tests/Feature/Auth/EmailVerificationTest.php`
- **Priority**: P1 (High)
- **Description**: Verify email verification process
- **Status**: ✅ Implemented (file exists, details not reviewed)

## 1.4 Password Management

#### TC-PWD-001: Password Reset Test
- **Type**: Feature Test
- **File**: `tests/Feature/Auth/PasswordResetTest.php`
- **Priority**: P1 (High)
- **Description**: Verify password reset functionality
- **Status**: ✅ Implemented (file exists, details not reviewed)

#### TC-PWD-002: Password Update Test
- **Type**: Feature Test
- **File**: `tests/Feature/Auth/PasswordUpdateTest.php`
- **Priority**: P1 (High)
- **Description**: Verify password update functionality
- **Status**: ✅ Implemented (file exists, details not reviewed)

#### TC-PWD-003: Password Confirmation Test
- **Type**: Feature Test
- **File**: `tests/Feature/Auth/PasswordConfirmationTest.php`
- **Priority**: P1 (High)
- **Description**: Verify password confirmation for sensitive operations
- **Status**: ✅ Implemented (file exists, details not reviewed)

---

**Last Updated**: January 2026
