describe('Plugin setup of "References for Chapters" plugin', function () {
    it('Enables "References for Chapters"', function () {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.contains('a', 'Website').click();

		cy.waitJQuery();
		cy.get('#plugins-button').click();

		cy.get('input[id^=select-cell-referencesforchaptersplugin]').check();
		cy.get('input[id^=select-cell-referencesforchaptersplugin]').should('be.checked');
    });
});