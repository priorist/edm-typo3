<?php
if (!defined('TYPO3')) {
	die('Access denied.');
}

call_user_func(
	function () {
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
			'@import "EXT:edm-typo3/Configuration/TSConfig/Page/Page.tsconfig"'
		);
		
		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Eventlist',
			[
				\Priorist\EdmTypo3\Controller\EventController::class => 'list',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Eventdetail',
			[
				\Priorist\EdmTypo3\Controller\EventController::class => 'detail',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Eventsearch',
			[
				\Priorist\EdmTypo3\Controller\EventController::class => 'search',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Locationlist',
			[
				\Priorist\EdmTypo3\Controller\LocationController::class => 'list',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Locationdetail',
			[
				\Priorist\EdmTypo3\Controller\LocationController::class => 'detail',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Enrollmentnew',
			[
				\Priorist\EdmTypo3\Controller\EnrollmentController::class => 'new',
			],
			// non-cacheable actions
			[
				\Priorist\EdmTypo3\Controller\EnrollmentController::class => 'new',
			]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Staffdetail',
			[
				\Priorist\EdmTypo3\Controller\StaffController::class => 'detail',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Lecturerlist',
			[
				\Priorist\EdmTypo3\Controller\LecturerController::class => 'list',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Lecturerdetail',
			[
				\Priorist\EdmTypo3\Controller\LecturerController::class => 'detail',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Formerrorsenderrormessage',
			[
				\Priorist\EdmTypo3\Controller\FormErrorController::class => 'sendErrorMessage',
			],
			// non-cacheable actions
			[]
		);
	}
);
