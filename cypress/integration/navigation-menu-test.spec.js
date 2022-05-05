// test-navigation-menu.spec.js created with Cypress
//
// Start writing your Cypress tests below!
// If you're unfamiliar with how Cypress works,
// check out the link below and learn how to write your first test:
// https://on.cypress.io/writing-first-test

it('tests the navigation menu', () => {
	cy.visit('tests/_site/index.html')

	cy.get('#main-navigation').should('be.visible')
  
	cy.get('#main-navigation-links').should('be.visible')
	cy.get('#main-navigation-links').find('a').should('have.length', 4)

	// Test the first button is home and is active
	cy.get('#main-navigation-links').find('a').eq(0).should('have.attr', 'href', 'index.html')
	cy.get('#main-navigation-links').find('a').eq(0).should('contain', 'Home')
	cy.get('#main-navigation-links').find('a').eq(0).should('have.attr', 'aria-current')

	// Test that clicking a link takes you to the correct page
	cy.get('#main-navigation-links').find('a').eq(2).click()
	cy.url().should('include', 'blade.html')
})

it('tests the mobile navigation menu', () => {
	cy.viewport('iphone-6')
	cy.visit('tests/_site/index.html')

	cy.get('#main-navigation-links').should('not.be.visible')

	cy.get('#navigation-toggle-button').should('be.visible')
	cy.get('#navigation-toggle-button').click()

	cy.get('#main-navigation-links').should('be.visible')
	cy.get('#navigation-toggle-button').click()
	cy.get('#main-navigation-links').should('not.be.visible')
})