export class FavoritesPage {
  constructor() {
    this.url = '/favorites'
  }

  visit() {
    cy.visit(this.url)
    return this
  }

  heading() {
    return cy.contains('My Favorites')
  }

  assertVisible() {
    this.heading().should('be.visible')
    return this
  }
}
