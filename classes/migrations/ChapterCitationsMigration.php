<?php

namespace APP\plugins\generic\referencesForChapters\classes\migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChapterCitationsMigration extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chapter_citations')) {
            Schema::create('chapter_citations', function (Blueprint $table) {
                $table->comment('A citation made by an associated chapter');

                $table->bigInteger('chapter_citation_id')->autoIncrement();
                $table->bigInteger('chapter_id');
                $table->text('raw_citation');
                $table->bigInteger('seq')->default(0);

                $table->foreign('chapter_id', 'citations_chapter')
                    ->references('chapter_id')
                    ->on('submission_chapters')
                    ->onDelete('cascade');
                $table->index(['chapter_id'], 'citations_chapter');
                $table->unique(['chapter_id', 'seq'], 'citations_chapter_seq');
            });
        }
    }
}
