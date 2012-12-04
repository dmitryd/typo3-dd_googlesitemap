<?php

########################################################################
# Extension Manager/Repository config file for ext "dd_googlesitemap".
#
# Auto generated 20-02-2012 22:30
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Google sitemap',
	'description' => 'High performance Google sitemap implementation that avoids typical errors by other similar extensions',
	'category' => 'fe',
	'author' => 'Dmitry Dulepov',
	'author_email' => 'dmitry.dulepov@gmail.com',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'pages',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'SIA "ACCIO"',
	'version' => '1.0.6',
	'constraints' => array(
		'depends' => array(
			'TYPO3' => '4.5.0-6.0.99'
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:14:{s:9:"ChangeLog";s:4:"608b";s:32:"class.tx_ddgooglesitemap_eid.php";s:4:"263a";s:34:"class.tx_ddgooglesitemap_pages.php";s:4:"e437";s:36:"class.tx_ddgooglesitemap_tcemain.php";s:4:"3528";s:35:"class.tx_ddgooglesitemap_ttnews.php";s:4:"c22e";s:12:"ext_icon.gif";s:4:"0709";s:17:"ext_localconf.php";s:4:"9e3e";s:14:"ext_tables.php";s:4:"397e";s:14:"ext_tables.sql";s:4:"024e";s:24:"ext_typoscript_setup.txt";s:4:"71ba";s:14:"doc/manual.sxw";s:4:"da82";s:56:"renderers/class.tx_ddgooglesitemap_abstract_renderer.php";s:4:"0dc9";s:52:"renderers/class.tx_ddgooglesitemap_news_renderer.php";s:4:"ea3b";s:54:"renderers/class.tx_ddgooglesitemap_normal_renderer.php";s:4:"c6ac";}',
	'suggests' => array(
	),
);

?>