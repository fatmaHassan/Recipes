export class LoginPage {
  constructor() {
    this.url = '/login'
  }

  visit() {
    cy.visit(this.url)
    return this
  }

  emailInput() {
    return cy.get('input[name="email"]')
  }

  passwordInput() {
    return cy.get('input[name="password"]')
  }

  submitButton() {
    return cy.get('button[type="submit"]')
  }

  fillEmail(email) {
    this.emailInput().clear().type(email)
    return this
  }

  fillPassword(password) {
    this.passwordInput().clear().type(password)
    return this
  }

  submit() {
    this.submitButton().click()
    return this
  }

  login(email, password) {
    this.visit()
    this.fillEmail(email)
    this.fillPassword(password)
    this.submit()
    return this
  }
}
