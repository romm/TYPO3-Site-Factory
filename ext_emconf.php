<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "site_factory".
 *
 * Auto generated 14-11-2016 15:24
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'Site Factory',
  'state' => 'beta',
  'version' => '0.3.0',
  'description' => 'Replicate and modify an existing website model very easily with a flexible and lean design. Read the code examples to understand and master all the TypoScript configuration, or extend the existing duplication processes. Based on freesite (created by Kasper Skårhøj) this project was originaly conceived by Cyril Wolfangel and is developped and maintained by Romain Canon. Join the project on https://github.com/romaincanon/TYPO3-Site-Factory',
  'category' => 'module',
  'constraints' => 
  array (
    'depends' => 
    array (
      'php' => '5.5.0',
      'extbase' => '7.2',
      'fluid' => '7.2',
      'typo3' => '7.2.0-8.7.99',
    ),
    'conflicts' => 
    array (
    ),
    'suggests' => 
    array (
    ),
  ),
  'author' => 'Romain CANON',
  'author_email' => 'romain.hydrocanon@gmail.com',
  'uploadfolder' => true,
  'createDirs' => 'uploads/tx_sitefactory/_processed_/',
  'clearCacheOnLoad' => 1,
  'clearcacheonload' => true,
  'author_company' => NULL,
);

