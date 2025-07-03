<?php
if (!defined('TYPO3')) {
	die('Access denied.');
}

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

call_user_func(
	function () {

		ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Eventlist',
			[
				\Priorist\EdmTypo3\Controller\EventController::class => 'list',
			],
			// non-cacheable actions
			[],
			ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
		);

		ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Eventdetail',
			[
				\Priorist\EdmTypo3\Controller\EventController::class => 'detail',
			],
			// non-cacheable actions
			[
				\Priorist\EdmTypo3\Controller\EventController::class => 'detail'
			],
			ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
		);

		ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Eventsearch',
			[
				\Priorist\EdmTypo3\Controller\EventController::class => 'search',
			],
			// non-cacheable actions
			[],
			ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
		);

		ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Locationlist',
			[
				\Priorist\EdmTypo3\Controller\LocationController::class => 'list',
			],
			// non-cacheable actions
			[],
			ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
		);

		ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Locationdetail',
			[
				\Priorist\EdmTypo3\Controller\LocationController::class => 'detail',
			],
			// non-cacheable actions
			[],
			ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
		);

		ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Enrollmentnew',
			[
				\Priorist\EdmTypo3\Controller\EnrollmentController::class => 'new',
			],
			// non-cacheable actions
			[
				\Priorist\EdmTypo3\Controller\EnrollmentController::class => 'new',
			],
			ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
		);

		ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Staffdetail',
			[
				\Priorist\EdmTypo3\Controller\StaffController::class => 'detail',
			],
			// non-cacheable actions
			[],
			ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
		);

		ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Lecturerlist',
			[
				\Priorist\EdmTypo3\Controller\LecturerController::class => 'list',
			],
			// non-cacheable actions
			[],
			ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
		);

		ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Lecturerdetail',
			[
				\Priorist\EdmTypo3\Controller\LecturerController::class => 'detail',
			],
			// non-cacheable actions
			[],
			ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
		);

		ExtensionUtility::configurePlugin(
			'EdmTypo3',
			'Formerrorsenderrormessage',
			[
				\Priorist\EdmTypo3\Controller\FormErrorController::class => 'sendErrorMessage',
			],
			// non-cacheable actions
			[],
			ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
		);
	}
);
