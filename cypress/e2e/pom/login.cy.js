import { LoginPage } from '../../pages/login.page'
import { RegisterPage } from '../../pages/register.page'

describe('Login (POM)', () => {
  it('shows the login form and authenticates an existing user', () => {
    const email = `pom-login+${Date.now()}@example.com`
    const password = 'Password123!'
    const name = 'POM Login User'
    const registerPage = new RegisterPage()
    const loginPage = new LoginPage()

    registerPage.register({ name, email, password })
    cy.contains('Log Out').click({ force: true })

    loginPage.visit()
    loginPage.emailInput().should('be.visible')
    loginPage.passwordInput().should('be.visible')
    loginPage.submitButton().should('be.visible')

    loginPage.login(email, password)

    cy.url().should('not.include', '/login')
    cy.contains(name).should('be.visible')
  })
})
