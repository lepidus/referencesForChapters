import '../support/commands.js';

function beginSubmission(submissionData) {
    cy.get('input[name="locale"][value="en"]').click();
    cy.setTinyMceContent('startSubmission-title-control', submissionData.title);
    cy.get('input[name="workType"][value="2"]').click();
    
    cy.get('input[name="submissionRequirements"]').check();
    cy.get('input[name="privacyConsent"]').check();
    cy.contains('button', 'Begin Submission').click();
}

function detailsStep(submissionData) {
    cy.setTinyMceContent('titleAbstract-abstract-control-en', submissionData.abstract);
    submissionData.keywords.forEach(keyword => {
        cy.get('#titleAbstract-keywords-control-en').type(keyword, {delay: 0});
        cy.wait(500);
        cy.get('#titleAbstract-keywords-control-en').type('{enter}', {delay: 0});
    });
    cy.addChapters(submissionData.chapters);

    cy.contains('button', 'Continue').click();
}

function contributorsStep(submissionData) {
    submissionData.contributors.forEach(authorData => {
        cy.contains('button', 'Add Contributor').click();
        cy.get('input[name="givenName-en"]').type(authorData.given, {delay: 0});
        cy.get('input[name="familyName-en"]').type(authorData.family, {delay: 0});
        cy.get('input[name="email"]').type(authorData.email, {delay: 0});
        cy.get('select[name="country"]').select(authorData.country);
        
        cy.get('.modal__panel:contains("Add Contributor")').find('button').contains('Save').click();
        cy.waitJQuery();
    });

    cy.contains('button', 'Continue').click();
}

describe('Adds references to monograph chapters', function () {
    let submissionData;
    
    before(function() {
        submissionData = {
            title: 'God of War',
			abstract: 'Just a simple abstract',
			keywords: ['Greek mythology', 'Epic fantasy'],
            chapters: [
                {
                    'title': 'Prologue',
                    'subtitle': 'Kratos throws himself from the mountain into the sea',
                    'contributors': ['Michael Dawson']
                },
                {
                    'title': 'Chapter one',
                    'subtitle': 'Kratos meets the Hydra',
                    'contributors': ['Michael Dawson']
                }
            ],
            files: [
                {
                    'file': 'dummy.pdf',
                    'fileName': 'dummy.pdf',
                    'mimeType': 'application/pdf',
                    'genre': 'Preprint Text'
                }
            ]
		}
    });

    it('Creates new submission with chapters', function () {
        cy.login('mdawson', null, 'publicknowledge');
        // cy.get('#myQueue a:contains("New Submission")').click();
        cy.findSubmission('myQueue', submissionData.title);

        // beginSubmission(submissionData);
        detailsStep(submissionData);
    });
});