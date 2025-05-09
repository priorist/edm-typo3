<?php
defined('TYPO3') or die();

$extensionKey = 'EdmTypo3';
$extensionIcon = 'edm';
$pluginGroup = 'Education Manager (EDM)';

// Register Plugins in Backend
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Eventlist',
    'Veranstaltungen: Liste',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Eventdetail',
    'Veranstaltungen: Detailseite',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Eventsearch',
    'Veranstaltungen: Suche',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Locationlist',
    'Veranstaltungsorte: Liste',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Locationdetail',
    'Veranstaltungsorte: Detailseite',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Enrollmentnew',
    'Veranstaltungen: Anmeldung',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Staffdetail',
    'Kontaktperson: Detailseite',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Lecturerlist',
    'Dozierende: Liste',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Lecturerdetail',
    'Dozierende: Detailseite',
    $extensionIcon,
    $pluginGroup
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $extensionKey,
    'Formerrorsenderrormessage',
    'EDM: Anmelde-Fehler',
    $extensionIcon,
    $pluginGroup
);

// Add FlexForm to Plugin
foreach (['eventlist', 'eventdetail', 'eventsearch', 'staffdetail', 'locationlist'] as $plugin) {
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['edmtypo3_' . $plugin] = 'pi_flexform';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        'edmtypo3_' . $plugin,
        'FILE:EXT:edm-typo3/Configuration/FlexForms/' . ucfirst($plugin) . '.xml'
    );
}
