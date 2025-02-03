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

class ReferencesForChaptersPlugin extends GenericPlugin
{
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);

        if (Application::isUnderMaintenance()) {
            return $success;
        }

        // if ($success && $this->getEnabled($mainContextId)) {
        //     Hooks to be added
        // }

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
}
