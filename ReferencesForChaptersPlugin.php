<?php

/**
 * @file plugins/generic/referencesForChapters/ReferencesForChaptersPlugin.php
 *
 * Copyright (c) 2025 Lepidus Tecnologia
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @class ReferencesForChaptersPlugin
 *
 * @ingroup plugins_generic_referencesForChapters
 *
 */

namespace APP\plugins\generic\referencesForChapters;

use APP\core\Application;
use APP\plugins\generic\referencesForChapters\classes\chapterCitation\ChapterCitationDAO;
use APP\plugins\generic\referencesForChapters\classes\chapterCitation\ChapterReferencesService;
use APP\plugins\generic\referencesForChapters\classes\migrations\ChapterCitationsMigration;
use APP\template\TemplateManager;
use PKP\db\DAORegistry;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class ReferencesForChaptersPlugin extends GenericPlugin
{
    private const DEFAULT_THEME_PATH = 'default';

    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);

        if (Application::isUnderMaintenance()) {
            return $success;
        }

        if ($success && $this->getEnabled($mainContextId)) {
            Hook::add('chapterform::display', [$this, 'addChapterReferencesField']);
            Hook::add('chapterform::readuservars', [$this, 'setChapterFormToReadReferences']);
            Hook::add('chapterform::execute', [$this, 'setChapterFormToSaveReferences']);
            Hook::add('chapterdao::getAdditionalFieldNames', [$this, 'addReferencesSettingToChapter']);
            Hook::add('CatalogBookHandler::book', [$this, 'assignReferencesToChapterPage']);
            Hook::add('TemplateManager::display', [$this, 'addReferencesToChapterPage']);
        }

        return $success;
    }

    public function getDisplayName()
    {
        return __('plugins.generic.referencesForChapters.displayName');
    }

    public function getDescription()
    {
        return __('plugins.generic.referencesForChapters.description');
    }

    public function getInstallMigration()
    {
        return new ChapterCitationsMigration();
    }

    public function addChapterReferencesField($hookName, $params)
    {
        $chapterForm = $params[0];
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);

        $chapter = $chapterForm->getChapter();
        $chapterCitationsRaw = $chapter ? $chapter->getData('chapterCitationsRaw') : null;

        $templateMgr->assign('chapterCitationsRaw', $chapterCitationsRaw);
        $templateMgr->registerFilter("output", [$this, 'addChapterReferencesFieldFilter']);

        return Hook::CONTINUE;
    }

    public function addChapterReferencesFieldFilter($output, $templateMgr)
    {
        if (preg_match('/<p><span class="formRequired">/', $output, $matches, PREG_OFFSET_CAPTURE)) {
            $posMatch = $matches[0][1];
            $chapterReferencesField = $templateMgr->fetch($this->getTemplateResource('chapterReferencesField.tpl'));
            $output = substr_replace($output, $chapterReferencesField, $posMatch, 0);

            $templateMgr->unregisterFilter('output', [$this, 'addChapterReferencesFieldFilter']);
        }

        return $output;
    }

    public function setChapterFormToReadReferences($hookName, $params)
    {
        $formUserVars = &$params[1];
        $formUserVars[] = 'chapterCitationsRaw';
    }

    public function setChapterFormToSaveReferences($hookName, $params)
    {
        $chapterForm = &$params[0];
        $chapterDao = DAORegistry::getDAO('ChapterDAO');
        $service = new ChapterReferencesService($chapterDao, new ChapterCitationDAO());
        $service->saveChapterReferences($chapterForm);
    }

    public function addReferencesSettingToChapter($hookName, $chapterDao, &$settingsFields)
    {
        $settingsFields[] = 'chapterCitationsRaw';
    }

    public function assignReferencesToChapterPage($hookName, $params)
    {
        $request = $params[0];
        $chapter = $params[3];

        if (!$chapter || !$chapter->getData('chapterCitationsRaw')) {
            return Hook::CONTINUE;
        }

        $templateMgr = TemplateManager::getManager($request);
        $chapterCitationDao = new ChapterCitationDAO();
        $templateMgr->assign('chapterCitations', $chapterCitationDao->getByChapterId($chapter->getId()));

        return Hook::CONTINUE;
    }

    public function addReferencesToChapterPage($hookName, $params)
    {
        $templateMgr = $params[0];
        $template = $params[1];

        $context = Application::get()->getRequest()->getContext();
        $isChapterRequest = $templateMgr->getTemplateVars('isChapterRequest');

        if (
            $template == 'frontend/pages/book.tpl'
            && $isChapterRequest
            && $context->getData('themePluginPath') == self::DEFAULT_THEME_PATH
        ) {
            $templateMgr->registerFilter("output", [$this, 'addReferencesToChapterPageFilter']);
        }
    }

    public function addReferencesToChapterPageFilter($output, $templateMgr)
    {
        if (preg_match('/<\/div><!-- \.main_entry -->/', $output, $matches, PREG_OFFSET_CAPTURE)) {
            $match = $matches[0][0];
            $offset = $matches[0][1];

            $newOutput = substr($output, 0, $offset);
            $newOutput .= $templateMgr->fetch($this->getTemplateResource('frontend/chapterCitations.tpl'));
            $newOutput .= substr($output, $offset);
            $output = $newOutput;
            $templateMgr->unregisterFilter('output', [$this, 'addReferencesToChapterPageFilter']);
        }
        return $output;
    }
}
