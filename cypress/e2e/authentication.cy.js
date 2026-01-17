describe('Authentication', () => {
  it('User can register and login', () => {
    const email = `test${Date.now()}@example.com`
    const password = 'password123'
    const name = 'Test User'

    // Register
    cy.visit('/register')
    cy.get('input[name="name"]').type(name)
    cy.get('input[name="email"]').type(email)
    cy.get('input[name="password"]').type(password)
    cy.get('input[name="password_confirmation"]').type(password)
    cy.get('button[type="submit"]').click()

    // Should be redirected after registration
    cy.url().should('not.include', '/register')

    // Logout and login
    cy.contains('Log Out').click({ force: true })
    
    // Login
    cy.visit('/login')
    cy.get('input[name="email"]').type(email)
    cy.get('input[name="password"]').type(password)
    cy.get('button[type="submit"]').click()

    // Should be logged in
    cy.url().should('not.include', '/login')
    cy.contains(name).should('be.visible')
  })
})
