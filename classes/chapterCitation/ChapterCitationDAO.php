<?php

namespace APP\plugins\generic\referencesForChapters\classes\chapterCitation;

use PKP\db\DAO;
use Illuminate\Support\Facades\DB;
use PKP\citation\CitationListTokenizerFilter;

class ChapterCitationDAO extends DAO
{
    public $table = 'chapter_citations';

    public function insertObject($chapterCitation)
    {
        $seq = $chapterCitation->getSequence();
        if (!(is_numeric($seq) && $seq > 0)) {
            $lastSeq = DB::table($this->table)
                ->where('chapter_id', '=', $chapterCitation->getChapterId())
                ->max('seq');
            $chapterCitation->setSequence($lastSeq ? $lastSeq + 1 : 1);
        }

        DB::table($this->table)->insert([
            'chapter_id' => $chapterCitation->getChapterId(),
            'raw_citation' => $chapterCitation->getRawCitation(),
            'seq' => $chapterCitation->getSequence()
        ]);

        return $this->getInsertId();
    }

    public function getByChapterId($chapterId)
    {
        $result = DB::table($this->table)
            ->where('chapter_id', '=', $chapterId)
            ->get();

        $chapterCitations = [];
        foreach ($result as $row) {
            $chapterCitations[] = $this->fromRow(get_object_vars($row));
        }

        return $chapterCitations;
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
        DB::table($this->table)
            ->where('chapter_id', '=', $chapterId)
            ->delete();
    }

    public function newDataObject()
    {
        return new ChapterCitation();
    }

    private function fromRow($row)
    {
        $chapterCitation = $this->newDataObject();
        $chapterCitation->setId((int) $row['chapter_citation_id']);
        $chapterCitation->setChapterId((int) $row['chapter_id']);
        $chapterCitation->setRawCitation($row['raw_citation']);
        $chapterCitation->setSequence((int) $row['seq']);

        return $chapterCitation;
    }
}
