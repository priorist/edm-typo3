<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Education Manager (EDM)',
	'description' => 'Provides several plugins to render data from EDM in TYPO3.',
	'category' => 'plugin',
	'author' => 'priorist GmbH',
	'author_email' => 'contact@priorist.com',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 1,
	'version' => '0.9.4',
	'constraints' => array(
		'depends' => array(
			'typo3' => '9.5.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		)
	)
);
