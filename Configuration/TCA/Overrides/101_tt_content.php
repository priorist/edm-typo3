<?php
defined('TYPO3_MODE') or die();

// Register Plugin in Backend
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Priorist.Edm',
    'Pi1',
    'EDM',
    'EXT:edm/ext_icon.svg'
);

// Add FlexForm to Plugin
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['edm_pi1'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'edm_pi1',
    'FILE:EXT:edm/Configuration/FlexForms/Plugin.xml'
);