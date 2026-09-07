import { IngredientsPage } from '../../pages/ingredients.page'
import { RecipeSearchPage } from '../../pages/recipe-search.page'
import { RegisterPage } from '../../pages/register.page'

describe('Recipe Search (POM)', () => {
  it('allows a user to search recipes by ingredient', () => {
    const email = `pom-search+${Date.now()}@example.com`
    const password = 'Password123!'
    const name = 'POM Search User'
    const ingredientName = 'Chicken'

    const registerPage = new RegisterPage()
    const ingredientsPage = new IngredientsPage()
    const recipeSearchPage = new RecipeSearchPage()

    registerPage.register({ name, email, password })

    ingredientsPage.visit()
    ingredientsPage.addIngredient(ingredientName)

    recipeSearchPage.visit()
    recipeSearchPage.selectIngredient(ingredientName)
    recipeSearchPage.search()
    recipeSearchPage.assertSearchResults()
  })
})
