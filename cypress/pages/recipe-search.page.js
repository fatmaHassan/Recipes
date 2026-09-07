export class RecipeSearchPage {
  constructor() {
    this.url = '/dashboard'
  }

  visit() {
    cy.visit(this.url)
    return this
  }

  selectIngredient(name) {
    cy.get(`input[type="checkbox"][value="${name}"]`).check()
    return this
  }

  search() {
    cy.contains('Search Recipes').click()
    return this
  }

  assertSearchResults() {
    cy.url().should('include', '/recipes')
    cy.contains('Recipe Search Results').should('be.visible')
    return this
  }
}
