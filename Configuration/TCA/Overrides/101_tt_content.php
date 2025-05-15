<?php
defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

$extensionKey = 'EdmTypo3';
$extensionIcon = 'edm';
$pluginGroup = 'Education Manager (EDM)';

/**
 * List of plugin configurations for TYPO3 tt_content overrides.
 *
 * Each plugin is represented as an array with the following structure:
 * [
 *     string $identifier,           // Unique plugin identifier
 *     string $label,                // Human-readable label (German)
 *     bool   $registerFlexForm      // Whether the plugin needs to register a FlexForm
 * ]
 */
$plugins = [
    ['Eventlist', 'Veranstaltungen: Liste', 'Filterbare Veranstaltungsliste.', true],
    ['Eventdetail', 'Veranstaltungen: Detailseite', 'Detailseite einer Veranstaltung.', true],
    ['Eventsearch', 'Veranstaltungen: Suche', 'Veranstaltungsliste, die zusätzlich Filterdaten bereitstellt.', true],
    ['Enrollmentnew', 'Veranstaltungen: Anmeldung', 'Anmeldeseite von Veranstaltungen.', false],
    ['Locationlist', 'Veranstaltungsorte: Liste', 'Filterbare Liste von Veranstaltungsorten.', true],
    ['Locationdetail', 'Veranstaltungsorte: Detailseite', 'Detailseite eines Veranstaltungsortes.', false],
    ['Staffdetail', 'Kontaktperson: Detailseite', 'Detailansicht einer Kontaktperson.', true],
    ['Lecturerlist', 'Dozierende: Liste', 'Filterbare Liste von Dozierenden.', false],
    ['Lecturerdetail', 'Dozierende: Detailseite', 'Detailseite eines Dozierenden.', false],
    ['Formerrorsenderrormessage', 'EDM: Anmelde-Fehler', 'Wird genutzt um per E-Mail über einen Anmeldefehler zu informieren.', false],
];

foreach ($plugins as [$name, $label, $description, $registerFlexForm]) {
    // Register the plugin with TYPO3
    $pluginSignature = ExtensionUtility::registerPlugin(
        $extensionKey,
        $name,
        $label,
        $extensionIcon,
        $pluginGroup,
        $description,
    );

    // Register flexform configuration if needed
    if ($registerFlexForm === true) {
        ExtensionManagementUtility::addToAllTCAtypes(
            'tt_content',
            '--div--;Configuration,pi_flexform,',
            $pluginSignature,
            'after:subheader',
        );
        ExtensionManagementUtility::addPiFlexFormValue(
            '*',
            'FILE:EXT:edm-typo3/Configuration/FlexForms/' . $name . '.xml',
            $pluginSignature
        );
    }
}
