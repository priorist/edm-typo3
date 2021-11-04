<?php
defined('TYPO3_MODE') or die();

// Register Plugins in Backend
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Edm',
    'Pieventlist',
    'Veranstaltungen: Liste',
    'EXT:edm/ext_icon.svg',
    'Education Manager (EDM)'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Edm',
    'Pieventdetail',
    'Veranstaltungen: Detailseite',
    'EXT:edm/ext_icon.svg',
    'Education Manager (EDM)'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Edm',
    'Pieventsearch',
    'Veranstaltungen: Suche',
    'EXT:edm/ext_icon.svg',
    'Education Manager (EDM)'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Edm',
    'Pilocationlist',
    'Veranstaltungsorte: Liste',
    'EXT:edm/ext_icon.svg',
    'Education Manager (EDM)'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Edm',
    'Pilocationdetail',
    'Veranstaltungsorte: Detailseite',
    'EXT:edm/ext_icon.svg',
    'Education Manager (EDM)'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Edm',
    'Pienrollmentnew',
    'Veranstaltungen: Anmeldung',
    'EXT:edm/ext_icon.svg',
    'Education Manager (EDM)'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Edm',
    'Piparticipantloginlogout',
    'Teilnehmer-Login',
    'EXT:edm/ext_icon.svg',
    'Education Manager (EDM)'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Edm',
    'Pistaffdetail',
    'Kontaktperson: Detailseite',
    'EXT:edm/ext_icon.svg',
    'Education Manager (EDM)'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Edm',
    'Pilecturerlist',
    'Dozierende: Liste',
    'EXT:edm/ext_icon.svg',
    'Education Manager (EDM)'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Edm',
    'Pilecturerdetail',
    'Dozierende: Detailseite',
    'EXT:edm/ext_icon.svg',
    'Education Manager (EDM)'
);

// Add FlexForm to Plugin
foreach (['pieventlist', 'pieventdetail', 'pieventsearch', 'pistaffdetail'] as $plugin) {
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['edm_' . $plugin] = 'pi_flexform';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        'edm_' . $plugin,
        'FILE:EXT:edm/Configuration/FlexForms/' . ucfirst($plugin) . '.xml'
    );
}
