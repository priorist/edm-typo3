<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

call_user_func(
	function () {
		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Pieventlist',
			[
				\Priorist\EdmTypo3\Controller\EventController::class => 'list',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Pieventdetail',
			[
				\Priorist\EdmTypo3\Controller\EventController::class => 'detail',
			],
			// non-cacheable actions
			[
				\Priorist\EdmTypo3\Controller\EventController::class => 'detail',
			]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Pieventsearch',
			[
				\Priorist\EdmTypo3\Controller\EventController::class => 'search',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Pilocationlist',
			[
				\Priorist\EdmTypo3\Controller\LocationController::class => 'list',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Pilocationdetail',
			[
				\Priorist\EdmTypo3\Controller\LocationController::class => 'detail',
			],
			// non-cacheable actions
			[
				\Priorist\EdmTypo3\Controller\LocationController::class => 'detail',
			]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Pienrollmentnew',
			[
				\Priorist\EdmTypo3\Controller\EnrollmentController::class => 'new',
			],
			// non-cacheable actions
			[
				\Priorist\EdmTypo3\Controller\EnrollmentController::class => 'new',
			]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Piparticipantloginlogout',
			[
				\Priorist\EdmTypo3\Controller\ParticipantController::class => 'login, logout',
			],
			// non-cacheable actions
			[
				\Priorist\EdmTypo3\Controller\ParticipantController::class => 'login, logout',
			]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Piparticipantstatus',
			[
				\Priorist\EdmTypo3\Controller\ParticipantController::class => 'status',
			],
			// non-cacheable actions
			[
				\Priorist\EdmTypo3\Controller\ParticipantController::class => 'status',
			]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Pistaffdetail',
			[
				\Priorist\EdmTypo3\Controller\StaffController::class => 'detail',
			],
			// non-cacheable actions
			[
				\Priorist\EdmTypo3\Controller\StaffController::class => 'detail',
			]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Pilecturerlist',
			[
				\Priorist\EdmTypo3\Controller\LecturerController::class => 'list',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Pilecturerdetail',
			[
				\Priorist\EdmTypo3\Controller\LecturerController::class => 'detail',
			],
			// non-cacheable actions
			[
				\Priorist\EdmTypo3\Controller\LecturerController::class => 'detail',
			]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Piformerrorsenderrormessage',
			[
				\Priorist\EdmTypo3\Controller\FormErrorController::class => 'sendErrorMessage',
			],
			// non-cacheable actions
			[
				\Priorist\EdmTypo3\Controller\FormErrorController::class => 'sendErrorMessage',
			]
		);
	}
);

$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

$iconRegistry->registerIcon(
	'edm',
	\TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
	['source' => 'EXT:edm/ext_icon.svg']
);
