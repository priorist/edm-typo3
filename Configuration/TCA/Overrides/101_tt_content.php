<?php
defined('TYPO3_MODE') or die();

$extensionKey = 'Edm';
$extensionIcon = 'EXT:edm/ext_icon.svg';
$pluginGroup = 'Education Manager (EDM)';

// Register Plugins in Backend
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Pieventlist',
    'Veranstaltungen: Liste',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Pieventdetail',
    'Veranstaltungen: Detailseite',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Pieventsearch',
    'Veranstaltungen: Suche',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Pilocationlist',
    'Veranstaltungsorte: Liste',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Pilocationdetail',
    'Veranstaltungsorte: Detailseite',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Pienrollmentnew',
    'Veranstaltungen: Anmeldung',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Piparticipantloginlogout',
    'Teilnehmer-Login',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Pistaffdetail',
    'Kontaktperson: Detailseite',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Pilecturerlist',
    'Dozierende: Liste',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Pilecturerdetail',
    'Dozierende: Detailseite',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Piformerrorsenderrormessage',
    'EDM: Anmelde-Fehler',
    $extensionIcon,
    $pluginGroup
);

// Add FlexForm to Plugin
foreach (['pieventlist', 'pieventdetail', 'pieventsearch', 'pistaffdetail', 'pilocationlist'] as $plugin) {
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['edm_' . $plugin] = 'pi_flexform';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        'edm_' . $plugin,
        'FILE:EXT:edm/Configuration/FlexForms/' . ucfirst($plugin) . '.xml'
    );
}
