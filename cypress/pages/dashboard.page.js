export class DashboardPage {
  constructor() {
    this.url = '/dashboard'
  }

  visit() {
    cy.visit(this.url)
    return this
  }

  welcomeMessage() {
    return cy.contains(/welcome back|dashboard/i).first()
  }

  ingredientCheckbox(name) {
    return cy.get(`input[type="checkbox"][value="${name}"]`)
  }

  searchButton() {
    return cy.contains('Search Recipes')
  }

  assertVisible() {
    this.welcomeMessage().should('be.visible')
    return this
  }
}
