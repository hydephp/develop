before(() => {
	// root-level hook
	// runs once before all tests
	cy.exec('php ../../hyde publish:homepage posts -n')
})
  

describe('tests blogging module', () => {
  it('homepage can be changed to blog post feed', () => {
    cy.visit('index.html')
    cy.contains('Latest Posts')
  })

  // blog posts can be scaffolded
  it('blog posts can be scaffolded and shows up in feed', () => {
	  cy.exec('php ../../hyde make:post -n --force')
    cy.visit('index.html')
	  cy.get('.text-2xl').should('contain', 'My New Post')
  })

  // blog posts can be clicked to lead to the post
  it('blog posts can be clicked to lead to the post', () => {
    cy.visit('index.html')
    cy.get('.text-2xl').click()
    cy.url().should('include', '/posts/my-new-post.html')
  })
})