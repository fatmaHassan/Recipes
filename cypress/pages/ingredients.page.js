export class IngredientsPage {
  constructor() {
    this.url = '/ingredients'
  }

  visit() {
    cy.visit(this.url)
    return this
  }

  nameInput() {
    return cy.get('input[name="name"]')
  }

  submitButton() {
    return cy.get('button[type="submit"]')
  }

  addIngredient(name) {
    this.nameInput().clear().type(name)
    this.submitButton().click()
    return this
  }
}
