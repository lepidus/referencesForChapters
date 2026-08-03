<?php

/**
 * @file plugins/generic/referencesForChapters/classes/chapterCitation/ChapterReferencesService.php
 *
 * Copyright (c) 2025-2026 Lepidus Tecnologia
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace APP\plugins\generic\referencesForChapters\classes\chapterCitation;

use APP\controllers\grid\users\chapter\form\ChapterForm;
use APP\monograph\ChapterDAO;
use Illuminate\Support\Facades\DB;

class ChapterReferencesService
{
    public function __construct(
        private ChapterDAO $chapterDao,
        private ChapterCitationDAO $chapterCitationDao
    ) {
    }

    public function saveChapterReferences(ChapterForm $chapterForm): void
    {
        DB::transaction(function () use ($chapterForm): void {
            $chapter = $chapterForm->getChapter();
            $oldChapterCitationsRaw = $chapter?->getData('chapterCitationsRaw');

            if ($chapter) {
                $chapter->setData('chapterCitationsRaw', $chapterForm->getData('chapterCitationsRaw'));
            } else {
                $chapter = $this->chapterDao->newDataObject();
                $chapter->setData('publicationId', $chapterForm->getPublication()->getId());
                $chapter->setTitle($chapterForm->getData('title'), null);
                $chapter->setSubtitle($chapterForm->getData('subtitle'), null);
                $chapter->setAbstract($chapterForm->getData('abstract'), null);
                $chapter->setDatePublished($chapterForm->getData('datePublished'));
                $chapter->setPages($chapterForm->getData('pages'));
                $chapter->setPageEnabled($chapterForm->getData('isPageEnabled'));
                $chapter->setLicenseUrl($chapterForm->getData('licenseUrl'));
                $chapter->setSequence(REALLY_BIG_NUMBER);
                $chapter->setData('chapterCitationsRaw', $chapterForm->getData('chapterCitationsRaw'));
                $chapterId = $this->chapterDao->insertChapter($chapter);
                $this->chapterDao->resequenceChapters($chapterForm->getPublication()->getId());
                $chapter->setId($chapterId);
            }

            if ($oldChapterCitationsRaw != $chapter->getData('chapterCitationsRaw')) {
                $this->chapterCitationDao->importChapterCitations(
                    $chapter->getId(),
                    $chapter->getData('chapterCitationsRaw')
                );
            }

            $chapterForm->setChapter($chapter);
        });
    }
}
