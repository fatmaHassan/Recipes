describe('Favorites', () => {
  beforeEach(() => {
    // Login before each test
    cy.login()
  })

  it('Authenticated user can add recipe to favorites', () => {
    // This test assumes a recipe exists and can be saved
    // In a real scenario, you might need to mock the API or use test data
    
    // Navigate to a recipe (assuming recipe ID 52772 exists)
    cy.visit('/recipes/52772')

    // Save the recipe first
    cy.contains('Save Recipe').click()

    // Add to favorites
    cy.contains('Add to Favorites').click()

    // Check favorites page
    cy.visit('/favorites')
    cy.contains('My Favorites').should('be.visible')
  })
})
