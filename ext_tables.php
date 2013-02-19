<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$tempColumns = Array (
	'tx_ddgooglesitemap_lastmod' => Array (
		'exclude' => 1,
		'label' => '',
		'config' => Array (
			'type' => 'passthrough',
		)
	),
	'tx_ddgooglesitemap_priority' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_priority',
		'displayCond' => 'FIELD:no_search:=:0',
		'config' => array(
			'type' => 'select',
			'items' => array(
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_priority.0', 0),
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_priority.1', 1),
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_priority.2', 2),
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_priority.3', 3),
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_priority.4', 4),
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_priority.5', 5),
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_priority.6', 6),
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_priority.7', 7),
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_priority.8', 8),
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_priority.8', 9),
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_priority.10', 10),
			)
		)
	),
);


t3lib_div::loadTCA('pages');
t3lib_extMgm::addTCAcolumns('pages', $tempColumns, 0);
t3lib_extMgm::addFieldsToPalette('pages', '2', 'tx_ddgooglesitemap_priority');

unset($tempColumn);

?>