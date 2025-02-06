<?php

use PKP\tests\PKPTestCase;
use APP\plugins\generic\referencesForChapters\classes\chapterCitation\ChapterCitation;

class ChapterCitationTest extends PKPTestCase
{
    public function testGettersAndSetters(): void
    {
        $chapterCitation = new ChapterCitation();
        $chapterCitation->setChapterId(123);
        $chapterCitation->setRawCitation('First reference');
        $chapterCitation->setSequence(1);

        $this->assertEquals(123, $chapterCitation->getChapterId());
        $this->assertEquals('First reference', $chapterCitation->getRawCitation());
        $this->assertEquals(1, $chapterCitation->getSequence());
    }

    public function testCleansRawCitation(): void
    {
        $chapterCitation = new ChapterCitation();
        $chapterCitation->setRawCitation('  First \reference   ');

        $this->assertEquals('First reference', $chapterCitation->getRawCitation());
    }
}
