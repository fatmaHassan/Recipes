import { IngredientsPage } from '../pages/ingredients.page'
import { RecipeSearchPage } from '../pages/recipe-search.page'

describe('Recipe Search', () => {
  beforeEach(() => {
    cy.login()
  })

  it('User can search recipes by selecting ingredients', () => {
    const ingredientsPage = new IngredientsPage()
    const recipeSearchPage = new RecipeSearchPage()

    ingredientsPage.visit()
    ingredientsPage.addIngredient('Chicken')

    recipeSearchPage.visit()
    recipeSearchPage.selectIngredient('Chicken')
    recipeSearchPage.search()
    recipeSearchPage.assertSearchResults()
  })
})
