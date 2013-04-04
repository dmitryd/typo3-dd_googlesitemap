<?php
if (!defined('TYPO3_MODE')) {
	exit;
}

// eID
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['dd_googlesitemap'] = 'EXT:' . $_EXTKEY . '/class.tx_ddgooglesitemap_eid.php';

if (TYPO3_MODE == 'BE') {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/class.tx_ddgooglesitemap_tcemain.php:&tx_ddgooglesitemap_tcemain';
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_ddgooglesitemap_indextask'] = array(
	'extension'        => $_EXTKEY,
	'title'            => 'LLL:EXT:dd_googlesitemap/locallang.xml:scheduler.title',
	'description'      => 'LLL:EXT:dd_googlesitemap/locallang.xml:scheduler.description',
	'additionalFields' => 'tx_ddgooglesitemap_additionalfieldsprovider'
);

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dd_googlesitemap']['sitemap']['pages'] = 'tx_ddgooglesitemap_pages->main';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dd_googlesitemap']['sitemap']['news'] = 'tx_ddgooglesitemap_ttnews->main';

?>