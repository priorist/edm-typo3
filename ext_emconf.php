<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Education Manager (EDM)',
	'description' => 'Provides several plugins to render data from EDM in TYPO3.',
	'category' => 'plugin',
	'author' => 'priorist GmbH',
	'author_email' => 'contact@priorist.com',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 1,
	'version' => '3.0.1',
	'constraints' => [
		'depends' => [
			'typo3' => '12.4.0-13.4.99',
		],
	],
	'autoload' => [
		'psr-4' => [
			'Priorist\\EdmTypo3\\' => 'Classes',
		],
	],
);
