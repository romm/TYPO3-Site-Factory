<?php
$EM_CONF[$_EXTKEY] = [
    'title'       => 'Site Factory',
    'state'       => 'beta',
    'version'     => '0.2.0',
    'description' => 'Replicate and modify an existing website model very easily with a flexible and lean design. Read the code examples to understand and master all the TypoScript configuration, or extend the existing duplication processes. Based on freesite (created by Kasper Skårhøj) this project was originaly conceived by Cyril Wolfangel and is developped and maintained by Romain Canon. Join the project on https://github.com/romaincanon/TYPO3-Site-Factory',
    'category'    => 'module',

    'constraints' => [
        'depends'   => [
            'php'     => '5.5.0',
            'extbase' => '6.2',
            'fluid'   => '6.2',
            'typo3'   => '6.2.0-7.6.99'
        ],
        'conflicts' => [],
        'suggests'  => []
    ],

    'author'         => 'Romain CANON',
    'author_email'   => 'romain.hydrocanon@gmail.com',

    'shy'              => '',
    'priority'         => '',
    'module'           => '',
    'internal'         => '',
    'uploadfolder'     => true,
    'createDirs'       => 'uploads/tx_sitefactory/_processed_/',
    'modify_tables'    => '',
    'clearCacheOnLoad' => 1,
    'lockType'         => ''
];
