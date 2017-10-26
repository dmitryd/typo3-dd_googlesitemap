<?php

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
			'renderType' => 'selectSingle',
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
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_priority.9', 9),
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_priority.10', 10),
			)
		)
	),
	'tx_ddgooglesitemap_change_frequency' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_change_frequency',
		'displayCond' => 'FIELD:no_search:=:0',
		'config' => array(
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => array(
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_change_frequency.calculate', ''),
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_change_frequency.always', 'always'),
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_change_frequency.hourly', 'hourly'),
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_change_frequency.daily', 'daily'),
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_change_frequency.weekly', 'weekly'),
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_change_frequency.monthly', 'monthly'),
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_change_frequency.yearly', 'yearly'),
				array('LLL:EXT:dd_googlesitemap/locallang.xml:pages.tx_ddgooglesitemap_change_frequency.never', 'never'),
			)
		)
	),
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
	'pages', 'miscellaneous', 'tx_ddgooglesitemap_priority, tx_ddgooglesitemap_change_frequency'
);

unset($tempColumn);
