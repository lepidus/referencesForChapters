describe('Plugin setup of "References for Chapters" plugin', function () {
    const pluginCheckbox = 'input[id^=select-cell-referencesforchaptersplugin]';

    function openPluginSettings() {
	cy.login('dbarnes', null, 'publicknowledge');
	cy.visit('index.php/publicknowledge/management/settings/website');
	cy.get('#plugins-button').should('be.visible').click();
    }

    it('Enables and disables "References for Chapters"', function () {
	openPluginSettings();
	cy.get(pluginCheckbox).check();
	cy.get(pluginCheckbox).should('be.checked');
	cy.get(pluginCheckbox).uncheck();
	cy.get('[data-cy="dialog"] button.text-negative').click();
	cy.location('hash').should('eq', '#plugins');
	cy.get(pluginCheckbox).should('not.be.checked');
    });

    it('Re-enables "References for Chapters"', function () {
	openPluginSettings();
	cy.get(pluginCheckbox).check();
	cy.get(pluginCheckbox).should('be.checked');
    });
});
