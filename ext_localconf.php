<?php
if (!defined('TYPO3_MODE')) {
	exit;
}

// eID
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['dd_googlesitemap'] = 'EXT:' . $_EXTKEY . '/class.tx_ddgooglesitemap_eid.php';

if (TYPO3_MODE == 'BE') {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/class.tx_ddgooglesitemap_tcemain.php:&tx_ddgooglesitemap_tcemain';
}

?>