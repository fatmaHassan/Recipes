// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************

import { LoginPage } from '../pages/login.page'
import { RegisterPage } from '../pages/register.page'

Cypress.Commands.add('login', (email = 'test@example.com', password = 'password') => {
  const page = new LoginPage()
  page.login(email, password)
})

Cypress.Commands.add('register', (name = 'Test User', email = 'test@example.com', password = 'password') => {
  const page = new RegisterPage()
  page.register({ name, email, password })
})
