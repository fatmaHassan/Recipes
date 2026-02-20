# Test Plan — Jira / Test Management

This document is the **source of truth** for test planning in Jira (e.g. with Zephyr or other test management apps). Use it to create or sync Test Plans, Test Cycles, and Test Cases. It is versioned in the repo alongside the rest of the testing docs.

---

## 1. Test Plan Overview

| Field | Value |
|-------|--------|
| **Test Plan Name** | Recipes App — Main Test Plan |
| **Project** | (Your Jira project key, e.g. REC) |
| **Scope** | Functional E2E, Smoke, Regression; Performance & Accessibility (planned) |
| **Last Updated** | 2026-02-18 |

---

## 2. Test Cycle Structure

Create the following **Test Cycles** (or folders) in your test management tool to mirror this structure:

| Cycle / Folder | Purpose | Status |
|----------------|---------|--------|
| **Smoke** | Critical path; run on every build/deploy | Active |
| **E2E / Regression** | Full user flows and UI; Playwright & Cypress | Active |
| **Performance** | Load, response time, core Web Vitals | Planned |
| **Accessibility** | WCAG, keyboard, screen readers, a11y tooling | Planned |

---

## 3. Test Cases by Cycle

### 3.1 Smoke

Critical path tests; tag `@smoke` in automation.

| # | Test Name | Objective | Priority | Automation |
|---|------------|-----------|----------|------------|
| 1 | Home page loads | Verify home page loads and shows correct title | High | `tests/e2e/home-guest.spec.js` |
| 2 | Navigation visible | Verify nav and login/register links are visible | High | `tests/e2e/home-guest.spec.js` |
| 3 | Login page loads | Verify login page title and form fields | High | `tests/e2e/login.spec.js` |
| 4 | Register page loads | Verify register page title and form fields | High | `tests/e2e/register.spec.js` |
| 5 | Dashboard after login | Verify dashboard or redirect after login | Critical | `tests/e2e/dashboard.spec.js` |
| 6 | Authenticated nav | Verify Ingredients/Dashboard links for logged-in user | High | `tests/e2e/dashboard.spec.js` |

### 3.2 E2E / Regression

| # | Test Name | Objective | Priority | Automation |
|---|------------|-----------|----------|------------|
| 1 | Register and login flow | Full register → logout → login | Critical | `cypress/e2e/authentication.cy.js` |
| 2 | Recipe search by ingredients | Add ingredient → search → results page | Critical | `cypress/e2e/recipe-search.cy.js` |
| 3 | Add recipe to favorites | Save recipe → add to favorites → view favorites | High | `cypress/e2e/favorites.cy.js` |
| 4 | Home page guest view | URL, title, heading, nav for guest | High | `tests/e2e/home-guest.spec.js` |
| 5 | Dashboard title and content | Dashboard title and welcome/dashboard content | High | `tests/e2e/dashboard.spec.js` |

*Feature/API tests (PHPUnit) are in `tests/Feature/` and can be added as separate test cases or linked from this plan.*

### 3.3 Performance (Planned)

To be added when performance tests are implemented (e.g. k6, Lighthouse, or Laravel Dusk + metrics).

| # | Test Name | Objective | Priority |
|---|------------|-----------|----------|
| 1 | Home page LCP | Home page meets LCP target (e.g. &lt; 2.5s) | High |
| 2 | Recipe search response time | Search API responds within SLA (e.g. &lt; 1s) | Critical |
| 3 | Dashboard load time | Dashboard loads within target under load | High |
| 4 | (TBD) | Add more scenarios as needed | — |

### 3.4 Accessibility (Planned)

To be added when accessibility tests are implemented (e.g. axe-core, Pa11y, or manual checks).

| # | Test Name | Objective | Priority |
|---|------------|-----------|----------|
| 1 | Home page WCAG 2.1 AA | No critical/serious axe violations on home | High |
| 2 | Login form keyboard & labels | Full keyboard navigation and labels | High |
| 3 | Recipe search a11y | Focus order, labels, and landmarks | High |
| 4 | (TBD) | Screen reader and contrast checks | — |

---

## 4. Labels and Components

Suggested **Labels** for filtering:

- `smoke`
- `e2e`
- `playwright`
- `cypress`
- `phpunit` (for Feature/Unit)
- `performance` (when added)
- `accessibility` (when added)

Suggested **Components** (if used in your Jira project):

- Authentication
- Recipes
- Ingredients
- Profile
- Favorites
- Navigation / UI

---

## 5. How to Use This

1. **Create Test Plan** in your tool and name it as in §1.
2. **Create Test Cycles** (or folders) as in §2: Smoke, E2E/Regression, Performance, Accessibility.
3. **Import or create Test Cases** using `docs/testing/test-cases-export.csv` (see below) or by creating cases manually from the tables in §3.
4. **Link automation** by adding the automation path (e.g. `tests/e2e/home-guest.spec.js`) in a custom field or in the test description.
5. **Version this file** in git when you add new cycles, cases, or automation paths.

### CSV Import

A CSV for import into Jira test management tools (e.g. Zephyr) is provided at:

**`docs/testing/test-cases-export.csv`**

It includes columns: **Name**, **Objective**, **Precondition**, **Folder** (cycle), **Priority**, **Labels**, **Step**, **Expected Result**, and **Automation path**. Use your tool’s “Import Test Cases” and map these columns as needed. Placeholder rows for Performance and Accessibility are included for future use.

---

## 6. Document Info

| Field | Value |
|-------|--------|
| **Document version** | 1.0 |
| **Last updated** | 2026-02-18 |
| **Maintained by** | Development team |
