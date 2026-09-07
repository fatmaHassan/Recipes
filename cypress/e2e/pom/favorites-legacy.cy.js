import { LoginPage } from '../pages/login.page'
import { RecipeDetailsPage } from '../pages/recipe-details.page'
import { FavoritesPage } from '../pages/favorites.page'

describe('Favorites', () => {
  beforeEach(() => {
    cy.login()
  })

  it('Authenticated user can add recipe to favorites', () => {
    const recipeDetailsPage = new RecipeDetailsPage(52772)
    const favoritesPage = new FavoritesPage()

    recipeDetailsPage.visit()
    recipeDetailsPage.saveRecipe()
    recipeDetailsPage.addToFavorites()

    favoritesPage.visit()
    favoritesPage.assertVisible()
  })
})
