<?php
if (!defined('TYPO3_MODE')) {
	exit;
}

// eID
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['dd_googlesitemap'] = 'EXT:dd_googlesitemap/Classes/Generator/EntryPoint.php';

if (TYPO3_MODE == 'BE') {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['dd_googlesitemap'] = 'DmitryDulepov\\DdGooglesitemap\\Hooks\\TceMain';
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['DmitryDulepov\\DdGooglesitemap\\Scheduler\\Task'] = array(
	'extension'        => 'dd_googlesitemap',
	'title'            => 'LLL:EXT:dd_googlesitemap/locallang.xml:scheduler.title',
	'description'      => 'LLL:EXT:dd_googlesitemap/locallang.xml:scheduler.description',
	'additionalFields' => 'DmitryDulepov\\DdGooglesitemap\\Scheduler\\AdditionalFieldsProvider'
);

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dd_googlesitemap']['sitemap']['pages'] = 'DmitryDulepov\\DdGooglesitemap\\Generator\\PagesSitemapGenerator->main';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dd_googlesitemap']['sitemap']['tt_news'] = 'DmitryDulepov\\DdGooglesitemap\\Generator\\TtNewsSitemapGenerator->main';

?>