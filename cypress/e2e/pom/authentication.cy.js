import { LoginPage } from '../pages/login.page'
import { RegisterPage } from '../pages/register.page'

describe('Authentication', () => {
  it('User can register and login', () => {
    const email = `test${Date.now()}@example.com`
    const password = 'password123'
    const name = 'Test User'

    const registerPage = new RegisterPage()
    const loginPage = new LoginPage()

    registerPage.register({ name, email, password })
    cy.contains('Log Out').click({ force: true })

    loginPage.login(email, password)

    cy.url().should('not.include', '/login')
    cy.contains(name).should('be.visible')
  })
})
