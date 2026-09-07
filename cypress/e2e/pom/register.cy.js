import { RegisterPage } from '../../pages/register.page'

describe('Register (POM)', () => {
  it('creates a new user account', () => {
    const email = `pom-register+${Date.now()}@example.com`
    const password = 'Password123!'
    const name = 'POM Register User'
    const registerPage = new RegisterPage()

    registerPage.visit()
    registerPage.nameInput().should('be.visible')
    registerPage.emailInput().should('be.visible')
    registerPage.passwordInput().should('be.visible')
    registerPage.passwordConfirmationInput().should('be.visible')
    registerPage.submitButton().should('be.visible')

    registerPage.register({ name, email, password })

    cy.url().should('not.include', '/register')
    cy.contains(name).should('be.visible')
  })
})
