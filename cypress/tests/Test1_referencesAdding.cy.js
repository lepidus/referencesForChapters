import '../support/commands.js';

function addChapter(chapter) {
    cy.waitJQuery();
    cy.get('a[id^="component-grid-users-chapter-chaptergrid-addChapter-button-"]:visible').click();
    cy.get('#editChapterForm').should('exist');

    cy.get('#editChapterForm input[id^="title-en-"]').type(chapter.title, {delay: 0, force: true});
    cy.get('#editChapterForm input[id^="subtitle-en-"]').type(chapter.subtitle, {delay: 0, force: true});
    chapter.references.forEach(reference => {
        cy.get('#editChapterForm textarea[name="chapterCitationsRaw"]').type(reference, {delay: 0, force: true});
        cy.get('#editChapterForm textarea[name="chapterCitationsRaw"]').type('{enter}', {delay: 0, force: true});
    });
    chapter.contributors.forEach(contributor => {
        cy.get('#editChapterForm label:contains("' + Cypress.$.escapeSelector(contributor) + '")').click({force: true});
    });

    cy.get('#editChapterForm button:contains("Save")').click({force: true});
    cy.contains('div', 'Your changes have been saved.').should('be.visible');
    cy.waitJQuery();
    cy.get('a.pkp_linkaction_editChapter').then(chapterLinks => {
        const matchingChapters = [...chapterLinks]
            .filter(chapterLink => chapterLink.textContent.includes(chapter.title));
        expect(matchingChapters).to.have.length(1);
    });
}

function beginSubmission(submissionData) {
    cy.setTinyMceContent('startSubmission-title-control', submissionData.title);
    cy.contains('span', 'Monograph: Authors are associated with the book as a whole.').click();
    cy.contains('label', 'English').click();
    cy.get('input[name="submissionRequirements"]').check();
    cy.get('input[name="privacyConsent"]').check();
    cy.contains('button', 'Begin Submission').click();
}

function detailsStep(submissionData) {
    cy.setTinyMceContent('titleAbstract-abstract-control-en', submissionData.abstract);
    submissionData.keywords.forEach(keyword => {
        cy.get('#titleAbstract-keywords-control-en').type(keyword, {delay: 0});
        cy.get('#titleAbstract-keywords-control-en').type('{enter}', {delay: 0});
    });
    submissionData.chapters.forEach(addChapter);

    cy.contains('button', 'Continue').click();
}

describe('Adds references to monograph chapters', function () {
    let submissionData;
    
    before(function() {
        submissionData = {
            title: `God of War ${Date.now()}`,
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
        cy.contains('New Submission').click();

        beginSubmission(submissionData);
        detailsStep(submissionData);
        cy.uploadSubmissionFiles(submissionData.files);
        Cypress._.times(3, () => {
            cy.contains('button', 'Continue').click();
        });

        cy.contains('button', 'Submit').click();
        cy.get('[data-cy="dialog"]').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');

        cy.logout();
        cy.login('dbarnes', null, 'publicknowledge');
        cy.findSubmission('active', submissionData.title);
        cy.clickDecision('Send to Internal Review');
        cy.recordDecisionSendToReview('Send to Internal Review', ['Michael Dawson'], ['dummy.pdf']);
        cy.isActiveStageTab('Internal Review');
        cy.assignReviewer('Paul Hudson');
        cy.clickDecision('Send to External Review');
        cy.recordDecisionSendToReview('Send to External Review', ['Michael Dawson'], []);
        cy.isActiveStageTab('External Review');
        cy.assignReviewer('Gonzalo Favio');
        cy.clickDecision('Accept Submission');
        cy.recordDecisionAcceptSubmission(['Michael Dawson'], [], []);
        cy.isActiveStageTab('Copyediting');
        cy.clickDecision('Send To Production');
        cy.recordDecisionSendToProduction(['Michael Dawson'], []);
        cy.isActiveStageTab('Production');
        cy.logout();
    });
    it('Display and editing of chapter references in workflow', function() {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.findSubmission('active', submissionData.title);

        cy.openWorkflowMenu('Chapters');

        submissionData.chapters.forEach(chapter => {
            cy.contains('a', chapter.title).click();
            cy.get('#editChapterForm').should('exist');
            chapter.references.forEach(chapterReference => {
                cy.get('#editChapterForm textarea[name="chapterCitationsRaw"]')
                    .invoke('val')
                    .should('include', chapterReference);
            });
            cy.get('[data-cy="active-modal"] button.DialogClose').click();
        });

        cy.contains('a', submissionData.chapters[1].title).click();
        cy.get('#editChapterForm').should('exist');
        cy.get('#editChapterForm textarea[name="chapterCitationsRaw"]')
            .type('{enter}Fifth reference', {delay: 0, force: true});
        cy.get('#editChapterForm button:contains("Save")').click({force: true});
        cy.contains('div', 'Your changes have been saved.').should('be.visible');
        cy.waitJQuery();

        cy.contains('a', submissionData.chapters[1].title).click();
        cy.get('#editChapterForm').should('exist');
        cy.get('#editChapterForm textarea[name="chapterCitationsRaw"]')
            .invoke('val')
            .should('include', 'Fifth reference');

        cy.get('#editChapterForm textarea[name="chapterCitationsRaw"]').clear({force: true});
        cy.get('#editChapterForm button:contains("Save")').click({force: true});
        cy.contains('div', 'Your changes have been saved.').should('be.visible');
        cy.waitJQuery();

        cy.contains('a', submissionData.chapters[1].title).click();
        cy.get('#editChapterForm').should('exist');
        cy.get('#editChapterForm textarea[name="chapterCitationsRaw"]').should('have.value', '');
        cy.get('[data-cy="active-modal"] button.DialogClose').click();
    });

    it('Removes a chapter and displays references on its public chapter page', function() {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.findSubmission('active', submissionData.title);

        cy.openWorkflowMenu('Chapters');

        cy.contains('a.pkp_linkaction_editChapter', submissionData.chapters[1].title)
            .closest('tr')
            .find('a.show_extras')
            .click();
        cy.get('a.pkp_linkaction_deleteChapter:visible').click();
        cy.get('[data-cy="dialog"] button.text-negative').click();
        cy.contains('a.pkp_linkaction_editChapter', submissionData.chapters[1].title).should('not.exist');

        cy.contains('a.pkp_linkaction_editChapter', submissionData.chapters[0].title).click();
        cy.get('#editChapterForm').should('exist');
        cy.get('#editChapterForm input[name="isPageEnabled"]').check({force: true});
        cy.get('#editChapterForm button:contains("Save")').click({force: true});
        cy.contains('div', 'Your changes have been saved.').should('be.visible');

        cy.get('button:contains("Publish")').click();
        cy.get('.pkpWorkflow__publishModal button:contains("Publish")').click();
        cy.contains('button', 'Unpublish').should('be.visible');

        cy.visit('/index.php/publicknowledge/en/catalog');
        cy.contains('a', submissionData.title).click();
        cy.contains('a', submissionData.chapters[0].title).click();
        cy.get('.item.references').within(() => {
            cy.contains('First reference').should('be.visible');
            cy.contains('Second reference').should('be.visible');
        });
    });
});
