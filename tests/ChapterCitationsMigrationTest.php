<?php

/**
 * @file plugins/generic/referencesForChapters/tests/ChapterCitationsMigrationTest.php
 *
 * Copyright (c) 2025-2026 Lepidus Tecnologia
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt
 */

use APP\plugins\generic\referencesForChapters\classes\migrations\ChapterCitationsMigration;
use Illuminate\Support\Facades\Schema;
use PKP\tests\PKPTestCase;

class ChapterCitationsMigrationTest extends PKPTestCase
{
    public function testMigrationCanRunAgainstAnExistingPluginSchema(): void
    {
        $migration = new ChapterCitationsMigration();

        $migration->up();
        $this->assertTrue(Schema::hasTable('chapter_citations'));

        $migration->up();
        $this->assertTrue(Schema::hasTable('chapter_citations'));
    }
}
