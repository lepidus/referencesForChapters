<?php

namespace APP\plugins\generic\referencesForChapters\classes\chapterCitation;

use PKP\db\DAO;
use Illuminate\Support\Facades\DB;
use PKP\citation\CitationListTokenizerFilter;
use ChapterCitation;

class ChapterCitationDAO extends DAO
{
    public function insertObject($chapterCitation)
    {
        // To be implemented
    }

    public function getByChapterId($chapterId)
    {
        $result = DB::table('chapter_citations')
            ->where('chapter_id', '=', $chapterId)
            ->get();
    }

    public function importChapterCitations($chapterId, $rawCitationList)
    {
        $this->deleteByChapterId($chapterId);

        $citationTokenizer = new CitationListTokenizerFilter();
        $citationStrings = $citationTokenizer->execute($rawCitationList);

        if (is_array($citationStrings)) {
            foreach ($citationStrings as $seq => $citationString) {
                if (!empty(trim($citationString))) {
                    $chapterCitation = new ChapterCitation();
                    $chapterCitation->setChapterId($chapterId);
                    $chapterCitation->setRawCitation($citationString);
                    $chapterCitation->setSequence($seq + 1);

                    $this->insertObject($chapterCitation);
                }
            }
        }
    }

    public function deleteByChapterId($chapterId)
    {
        DB::table('chapter_citations')
            ->where('chapter_id', '=', $chapterId)
            ->delete();
    }

    private function fromRow()
    {
        // To be implemented
    }
}
