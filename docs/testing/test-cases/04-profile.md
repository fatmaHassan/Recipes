# User Profile Test Cases

This document contains all test cases related to user profile management and account operations.

← [Back to Test Cases Index](../TEST_CASES.md)

---

## 4.1 Profile Viewing

#### TC-PROF-001: Profile Page Display
- **Type**: Feature Test
- **File**: `tests/Feature/ProfileTest.php::test_profile_page_is_displayed`
- **Priority**: P1 (High)
- **Description**: Verify profile page loads for authenticated users
- **Preconditions**: User is authenticated
- **Steps**:
  1. GET `/profile`
- **Expected Result**: 
  - HTTP status 200
  - Profile page is displayed
- **Status**: ✅ Implemented

## 4.2 Profile Updates

#### TC-PROF-002: Update Profile Information
- **Type**: Feature Test
- **File**: `tests/Feature/ProfileTest.php::test_profile_information_can_be_updated`
- **Priority**: P1 (High)
- **Description**: Verify users can update their profile information
- **Preconditions**: User is authenticated
- **Steps**:
  1. PATCH `/profile` with updated name and email
- **Expected Result**: 
  - Profile updated successfully
  - No session errors
  - Redirected to `/profile`
  - Database reflects updated values
  - Email verification status reset (if email changed)
- **Status**: ✅ Implemented

#### TC-PROF-003: Email Verification Status Unchanged
- **Type**: Feature Test
- **File**: `tests/Feature/ProfileTest.php::test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged`
- **Priority**: P2 (Medium)
- **Description**: Verify email verification status remains when email unchanged
- **Preconditions**: 
  - User is authenticated
  - User's email is verified
- **Steps**:
  1. PATCH `/profile` with same email address
- **Expected Result**: 
  - Profile updated successfully
  - Email verification status remains verified
- **Status**: ✅ Implemented

## 4.3 Account Deletion

#### TC-PROF-004: Delete User Account
- **Type**: Feature Test
- **File**: `tests/Feature/ProfileTest.php::test_user_can_delete_their_account`
- **Priority**: P0 (Critical)
- **Description**: Verify users can delete their own account
- **Preconditions**: User is authenticated
- **Steps**:
  1. DELETE `/profile` with correct password
- **Expected Result**: 
  - Account deleted successfully
  - No session errors
  - Redirected to home page (`/`)
  - User is logged out
  - User record removed from database
- **Status**: ✅ Implemented

#### TC-PROF-005: Account Deletion Requires Correct Password
- **Type**: Feature Test
- **File**: `tests/Feature/ProfileTest.php::test_correct_password_must_be_provided_to_delete_account`
- **Priority**: P0 (Critical)
- **Description**: Verify account deletion requires correct password
- **Preconditions**: User is authenticated
- **Steps**:
  1. DELETE `/profile` with incorrect password
- **Expected Result**: 
  - Account deletion fails
  - Session error for password field
  - Redirected back to `/profile`
  - User account still exists
- **Status**: ✅ Implemented

---

**Last Updated**: January 2026
