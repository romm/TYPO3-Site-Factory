<?php
$EM_CONF[$_EXTKEY] = array(
	'title'				=> 'Site Factory',
	'state'				=> 'beta',
	'version'			=> '0.1.1',
	'description'		=> 'Replicate and modify an existing website model very easily with a flexible and lean design. Read the code examples to understand and master all the TypoScript configuration, or extend the existing duplication processes. Based on freesite (created by Kasper Skårhøj) this project was originaly conceived by Cyril Wolfangel and is developped and maintained by Romain Canon. Join the project on https://github.com/romaincanon/TYPO3-Site-Factory',
	'category'			=> 'module',

	'constraints'		=> array(
		'depends'			=> array(
			'extbase'			=> '6.2',
			'fluid'				=> '6.2',
			'typo3'				=> '6.2.0-6.2.99'
		),
		'conflicts'			=> array(),
		'suggests'			=> array()
	),

	'author'			=> 'Romain CANON',
	'author_email'		=> 'romain.canon@exl-group.com',
	'author_company'	=> 'EXL Group',

	'shy'				=> '',
	'priority'			=> '',
	'module'			=> '',
	'internal'			=> '',
	'uploadfolder'		=> true,
	'createDirs'		=> 'uploads/tx_sitefactory/_processed_/',
	'modify_tables'		=> '',
	'clearCacheOnLoad'	=> 1,
	'lockType'			=> ''
);
