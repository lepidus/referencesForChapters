Cypress.Commands.add('findSubmission', function(tab, title) {
	if (tab === 'active') {
		cy.get('nav').contains('Active submissions').click();
	}
	cy.contains('table tr', title).within(() => {
		cy.contains('button', /^\s*View\s*$/).click({force: true});
	});
});
