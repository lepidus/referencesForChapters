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

    public function addChapterReferencesField($hookName, $params)
    {
        $chapterForm = $params[0];
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);

        $templateMgr->assign('chapter', $chapterForm->getChapter());
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
}
