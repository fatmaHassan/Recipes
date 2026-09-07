export class RecipeDetailsPage {
  constructor(recipeId) {
    this.url = `/recipes/${recipeId}`
  }

  visit() {
    cy.visit(this.url)
    return this
  }

  saveRecipeButton() {
    return cy.contains('Save Recipe')
  }

  addToFavoritesButton() {
    return cy.contains('Add to Favorites')
  }

  saveRecipe() {
    this.saveRecipeButton().click()
    return this
  }

  addToFavorites() {
    this.addToFavoritesButton().click()
    return this
  }
}
