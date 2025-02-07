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

    public function getCitationWithLinks()
    {
        $citation = $this->getRawCitation();
        if (stripos($citation, '<a href=') === false) {
            $citation = preg_replace_callback(
                '#(http|https|ftp)://[\d\w\.-]+\.[\w\.]{2,6}[^\s\]\[\<\>]*/?#',
                function ($matches) {
                    $trailingDot = in_array($char = substr($matches[0], -1), ['.', ',']);
                    $url = rtrim($matches[0], '.,');
                    return "<a href=\"{$url}\">{$url}</a>" . ($trailingDot ? $char : '');
                },
                $citation
            );
        }
        return $citation;
    }

    private function cleanCitationString($citationString)
    {
        $citationString = trim(stripslashes($citationString));
        $citationString = PKPString::regexp_replace('/[\s]+/', ' ', $citationString);

        return $citationString;
    }
}
