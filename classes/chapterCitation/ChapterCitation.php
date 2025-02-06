<?php

namespace APP\plugins\generic\referencesForChapters\classes\chapterCitation;

use PKP\core\DataObject;
use PKP\core\PKPString;

class ChapterCitation extends DataObject
{
    public function setChapterId(int $chapterId)
    {
        $this->setData('chapterId', $chapterId);
    }

    public function getChapterId(): int
    {
        return $this->getData('chapterId');
    }

    public function setRawCitation(string $rawCitation)
    {
        $rawCitation = $this->cleanCitationString($rawCitation);
        $this->setData('rawCitation', $rawCitation);
    }

    public function getRawCitation(): string
    {
        return $this->getData('rawCitation');
    }

    public function setSequence(int $sequence)
    {
        $this->setData('seq', $sequence);
    }

    public function getSequence(): int
    {
        return $this->getData('seq');
    }

    private function cleanCitationString($citationString)
    {
        $citationString = trim(stripslashes($citationString));
        $citationString = PKPString::regexp_replace('/[\s]+/', ' ', $citationString);

        return $citationString;
    }
}
