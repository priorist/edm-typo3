<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
	'@import "EXT:edm/Configuration/TSConfig/Page/ContentElementWizard.tsconfig">'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Priorist.Edm',
	'Pi1',
	[ // Comma-separated list of allowed actions per Controller. The first action of the first controller is the default action.
		'Event'				=> 'list,detail,search',
		'Location'		=> 'list,detail',
		'Enrollment'	=> 'new',
		'Participant'	=> 'login,logout,status',
		'Newsletter'	=> 'add',
		'Staff'				=> 'detail',
		'Lecturer'		=> 'list,detail',
		'FormError'		=> 'sendErrorMessage',
	],
	[ // specify which actions should not be cached
		'Event'				=> 'detail',
		'Location'		=> 'detail',
		'Enrollment'	=> 'new',
		'Participant'	=> 'login,logout,status',
		'Newsletter'	=> 'add,subscribe',
		'Staff'				=> 'detail',
		'Lecturer'		=> 'detail',
		'FormError'		=> 'sendErrorMessage',
	]
);

$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

$iconRegistry->registerIcon(
	'edm',
	\TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
	['source' => 'EXT:edm/ext_icon.svg']
);
