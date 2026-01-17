describe('Recipe Search', () => {
  beforeEach(() => {
    // Login before each test
    cy.login()
  })

  it('User can search recipes by selecting ingredients', () => {
    // First, add an ingredient
    cy.visit('/ingredients')
    cy.get('input[name="name"]').type('Chicken')
    cy.get('button[type="submit"]').click()

    // Go to dashboard
    cy.visit('/dashboard')

    // Select ingredient and search
    cy.get('input[type="checkbox"][value="Chicken"]').check()
    cy.contains('Search Recipes').click()

    // Should see recipe results page
    cy.url().should('include', '/recipes')
    cy.contains('Recipe Search Results').should('be.visible')
  })
})
