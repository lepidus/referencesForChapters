import '../support/commands.js';

function addChapter(chapter) {
    cy.waitJQuery();
    cy.get('a[id^="component-grid-users-chapter-chaptergrid-addChapter-button-"]:visible').click();
    cy.wait(1000);

    cy.get('#editChapterForm input[id^="title-en-"]').type(chapter.title, {delay: 0});
    cy.get('#editChapterForm input[id^="subtitle-en-"]').type(chapter.subtitle, {delay: 0});
    chapter.references.forEach(reference => {
        cy.get('#editChapterForm textarea[name="chapterCitationsRaw"]').type(reference, {delay: 0});
        cy.get('#editChapterForm textarea[name="chapterCitationsRaw"]').type('{enter}', {delay: 0});
    });
    chapter.contributors.forEach(contributor => {
        cy.get('#editChapterForm label:contains("' + Cypress.$.escapeSelector(contributor) + '")').click();
    });
    
    cy.get('div.pkp_modal_panel div:contains("Add Chapter")').click();
    cy.flushNotifications();

    cy.get('#editChapterForm button:contains("Save")').click();
    cy.get('div:contains("Your changes have been saved.")');
    cy.waitJQuery();
}

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
    submissionData.chapters.forEach(addChapter);

    cy.contains('button', 'Continue').click();
}

function changeAuthorEditPermission(authorName, option) {
    cy.contains('span', authorName).parent().siblings('.show_extras').first().click();
	cy.get('.pkp_linkaction_icon_edit_user:visible').click();
	
	if (option == 'check') {
		cy.get('input[name="canChangeMetadata"]').check();
	} else {
		cy.get('input[name="canChangeMetadata"]').uncheck();
	}
	cy.contains('#submitFormButton', 'OK').click();
	cy.contains('The stage assignment has been changed');
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
                    'contributors': ['Michael Dawson'],
                    'references': ['First reference', 'Second reference']
                },
                {
                    'title': 'Chapter one',
                    'subtitle': 'Kratos meets the Hydra',
                    'contributors': ['Michael Dawson'],
                    'references': ['Third reference', 'Fourth reference']
                }
            ],
            files: [
                {
                    'file': 'dummy.pdf',
                    'fileName': 'dummy.pdf',
                    'mimeType': 'application/pdf',
                    'genre': 'Book Manuscript'
                }
            ]
		}
    });

    it('Creates new submission with chapters having references', function() {
        cy.login('mdawson', null, 'publicknowledge');
        cy.get('#myQueue a:contains("New Submission")').click();

        beginSubmission(submissionData);
        detailsStep(submissionData);
        cy.uploadSubmissionFiles(submissionData.files);
        Cypress._.times(3, () => {
            cy.contains('button', 'Continue').click();
        });

        cy.contains('button', 'Submit').click();
        cy.get('.modal__panel:visible').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');
    });
    it('Display and editing of chapter references in workflow', function() {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.findSubmission('active', submissionData.title);
        changeAuthorEditPermission('Michael Dawson', 'check');
        cy.logout();
        
        cy.login('mdawson', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);

        cy.get('#publication-button').click();
        cy.get('#chapters-button').click();

        submissionData.chapters.forEach(chapter => {
            cy.contains('a', chapter.title).click();
            cy.wait(1000);
            chapter.references.forEach(chapterReference => {
                cy.get('#editChapterForm textarea[name="chapterCitationsRaw"]')
                    .invoke('val')
                    .should('include', chapterReference);
            });
            cy.get('#editChapterForm button:contains("Cancel")').click();
        });

        cy.contains('a', submissionData.chapters[1].title).click();
        cy.wait(1000);
        cy.get('#editChapterForm textarea[name="chapterCitationsRaw"]')
            .type('Fifth reference', {delay: 0});
        cy.get('#editChapterForm button:contains("Save")').click();
        cy.waitJQuery();
        cy.reload();

        cy.contains('a', submissionData.chapters[1].title).click();
        cy.wait(1000);
        cy.get('#editChapterForm textarea[name="chapterCitationsRaw"]')
            .invoke('val')
            .should('include', 'Fifth reference');
    });
});