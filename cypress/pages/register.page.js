export class RegisterPage {
  constructor() {
    this.url = '/register'
  }

  visit() {
    cy.visit(this.url)
    return this
  }

  nameInput() {
    return cy.get('input[name="name"]')
  }

  emailInput() {
    return cy.get('input[name="email"]')
  }

  passwordInput() {
    return cy.get('input[name="password"]')
  }

  passwordConfirmationInput() {
    return cy.get('input[name="password_confirmation"]')
  }

  submitButton() {
    return cy.get('button[type="submit"]')
  }

  fillName(name) {
    this.nameInput().clear().type(name)
    return this
  }

  fillEmail(email) {
    this.emailInput().clear().type(email)
    return this
  }

  fillPassword(password) {
    this.passwordInput().clear().type(password)
    return this
  }

  fillPasswordConfirmation(password) {
    this.passwordConfirmationInput().clear().type(password)
    return this
  }

  submit() {
    this.submitButton().click()
    return this
  }

  register({ name, email, password }) {
    this.visit()
    this.fillName(name)
    this.fillEmail(email)
    this.fillPassword(password)
    this.fillPasswordConfirmation(password)
    this.submit()
    return this
  }
}
