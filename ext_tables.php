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
);


t3lib_div::loadTCA('pages');
t3lib_extMgm::addTCAcolumns('pages', $tempColumns, 0);

unset($tempColumn);

?>