import { FavoritesPage } from '../../pages/favorites.page'
import { RecipeDetailsPage } from '../../pages/recipe-details.page'
import { RegisterPage } from '../../pages/register.page'

describe('Favorites (POM)', () => {
  it('lets an authenticated user save a recipe and add it to favorites', () => {
    const email = `pom-favorites+${Date.now()}@example.com`
    const password = 'Password123!'
    const name = 'POM Favorites User'
    const recipeId = 52772

    const registerPage = new RegisterPage()
    const recipeDetailsPage = new RecipeDetailsPage(recipeId)
    const favoritesPage = new FavoritesPage()

    registerPage.register({ name, email, password })

    recipeDetailsPage.visit()
    recipeDetailsPage.saveRecipe()
    recipeDetailsPage.addToFavorites()

    favoritesPage.visit()
    favoritesPage.assertVisible()
  })
})
