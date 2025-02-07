<?php

/**
 * @file plugins/generic/referencesForChapters/ReferencesForChaptersPlugin.php
 *
 * Copyright (c) 2025 Lepidus Tecnologia
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @class ReferencesForChaptersPlugin
 * @ingroup plugins_generic_referencesForChapters
 *
 */

namespace APP\plugins\generic\referencesForChapters;

use PKP\plugins\GenericPlugin;
use APP\core\Application;
use PKP\plugins\Hook;
use APP\template\TemplateManager;
use PKP\db\DAORegistry;
use APP\plugins\generic\referencesForChapters\classes\migrations\ChapterCitationsMigration;

class ReferencesForChaptersPlugin extends GenericPlugin
{
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
        $templateMgr->registerFilter("output", array($this, 'addChapterReferencesFieldFilter'));

        return Hook::CONTINUE;
    }

    public function addChapterReferencesFieldFilter($output, $templateMgr)
    {
        if (preg_match('/<p><span class="formRequired">/', $output, $matches, PREG_OFFSET_CAPTURE)) {
            $posMatch = $matches[0][1];
            $chapterReferencesField = $templateMgr->fetch($this->getTemplateResource('chapterReferencesField.tpl'));
            $output = substr_replace($output, $chapterReferencesField, $posMatch, 0);

            $templateMgr->unregisterFilter('output', array($this, 'addChapterReferencesFieldFilter'));
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
        $chapter = $chapterForm->getChapter();
        $chapterDao = DAORegistry::getDAO('ChapterDAO');

        if ($chapter) {
            $chapter->setData('chapterCitationsRaw', $chapterForm->getData('chapterCitationsRaw'));
        } else {
            $chapter = $chapterDao->newDataObject();
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
            $chapterId = $chapterDao->insertChapter($chapter);
            $chapterDao->resequenceChapters($chapterForm->getPublication()->getId());
            $chapter->setId($chapterId);
        }

        $chapterForm->setChapter($chapter);
    }

    public function addReferencesSettingToChapter($hookName, $chapterDao, &$settingsFields)
    {
        $settingsFields[] = 'chapterCitationsRaw';
    }
}
