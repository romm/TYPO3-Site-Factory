<?php
$EM_CONF[$_EXTKEY] = array(
	'title'				=> 'Site Factory',
	'state'				=> 'beta',
	'version'			=> '0.1.0',
	'description'		=> 'This extension offers a module that allows the duplication of an existing site.',
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
	'createDirs'		=> 'uploads/tx_sitefactory/_processed_',
	'modify_tables'		=> '',
	'clearCacheOnLoad'	=> 1,
	'lockType'			=> ''
);