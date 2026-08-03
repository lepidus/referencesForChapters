<?php

/**
 * @file plugins/generic/referencesForChapters/tests/ChapterReferencesServiceTest.php
 *
 * Copyright (c) 2025-2026 Lepidus Tecnologia
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt
 */

use APP\controllers\grid\users\chapter\form\ChapterForm;
use APP\monograph\ChapterDAO;
use APP\plugins\generic\referencesForChapters\classes\chapterCitation\ChapterCitationDAO;
use APP\plugins\generic\referencesForChapters\classes\chapterCitation\ChapterReferencesService;
use APP\publication\Publication;
use Illuminate\Support\Facades\DB;
use PKP\db\DAORegistry;
use PKP\tests\PKPTestCase;

class ChapterReferencesServiceTest extends PKPTestCase
{
    public function testRollsBackNewChapterWhenCitationImportFails(): void
    {
        $publicationId = DB::table('publications')->value('publication_id');
        $this->assertNotNull($publicationId, 'The OMP test database must contain a publication fixture.');

        $publication = $this->createMock(Publication::class);
        $publication->method('getId')->willReturn((int) $publicationId);

        $chapterForm = $this->createMock(ChapterForm::class);
        $chapterForm->method('getChapter')->willReturn(null);
        $chapterForm->method('getPublication')->willReturn($publication);
        $chapterForm->method('getData')->willReturnCallback(fn (string $field) => [
            'title' => ['en' => 'Transactional chapter'],
            'subtitle' => ['en' => ''],
            'abstract' => ['en' => ''],
            'datePublished' => null,
            'pages' => null,
            'isPageEnabled' => false,
            'licenseUrl' => null,
            'chapterCitationsRaw' => 'A reference that fails to import',
        ][$field]);
        $chapterForm->expects($this->never())->method('setChapter');

        $chapterCitationDao = $this->createMock(ChapterCitationDAO::class);
        $chapterCitationDao->expects($this->once())
            ->method('importChapterCitations')
            ->willThrowException(new RuntimeException('Citation import failed'));

        /** @var ChapterDAO $chapterDao */
        $chapterDao = DAORegistry::getDAO('ChapterDAO');
        $service = new ChapterReferencesService($chapterDao, $chapterCitationDao);

        DB::beginTransaction();
        try {
            $chaptersBefore = DB::table('submission_chapters')
                ->where('publication_id', $publicationId)
                ->count();

            $exception = null;
            try {
                $service->saveChapterReferences($chapterForm);
            } catch (RuntimeException $caughtException) {
                $exception = $caughtException;
            }

            $chaptersAfter = DB::table('submission_chapters')
                ->where('publication_id', $publicationId)
                ->count();

            $this->assertInstanceOf(RuntimeException::class, $exception);
            $this->assertSame($chaptersBefore, $chaptersAfter);
        } finally {
            DB::rollBack();
        }
    }
}
