<?php
if (!defined('TYPO3')) {
	die('Access denied.');
}

call_user_func(
	function () {
		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Eventlist',
			[
				\Priorist\EdmTypo3\Controller\EventController::class => 'list',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Eventdetail',
			[
				\Priorist\EdmTypo3\Controller\EventController::class => 'detail',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Eventsearch',
			[
				\Priorist\EdmTypo3\Controller\EventController::class => 'search',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Locationlist',
			[
				\Priorist\EdmTypo3\Controller\LocationController::class => 'list',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Locationdetail',
			[
				\Priorist\EdmTypo3\Controller\LocationController::class => 'detail',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
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
			'Priorist.Edm',
			'Staffdetail',
			[
				\Priorist\EdmTypo3\Controller\StaffController::class => 'detail',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Lecturerlist',
			[
				\Priorist\EdmTypo3\Controller\LecturerController::class => 'list',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Lecturerdetail',
			[
				\Priorist\EdmTypo3\Controller\LecturerController::class => 'detail',
			],
			// non-cacheable actions
			[]
		);

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Priorist.Edm',
			'Formerrorsenderrormessage',
			[
				\Priorist\EdmTypo3\Controller\FormErrorController::class => 'sendErrorMessage',
			],
			// non-cacheable actions
			[]
		);
	}
);
