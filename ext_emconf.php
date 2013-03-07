<?php

########################################################################
# Extension Manager/Repository config file for ext "dd_googlesitemap".
#
# Auto generated 07-03-2013 14:24
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
	'createDirs' => 'typo3temp/dd_googlesitemap',
	'modify_tables' => 'pages',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'SIA "ACCIO"',
	'version' => '1.1.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-6.0.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:19:{s:9:"ChangeLog";s:4:"822f";s:9:"README.md";s:4:"ee4d";s:32:"class.tx_ddgooglesitemap_eid.php";s:4:"d1c3";s:34:"class.tx_ddgooglesitemap_pages.php";s:4:"51cd";s:36:"class.tx_ddgooglesitemap_tcemain.php";s:4:"aedb";s:35:"class.tx_ddgooglesitemap_ttnews.php";s:4:"c00f";s:16:"ext_autoload.php";s:4:"aff4";s:12:"ext_icon.gif";s:4:"0709";s:17:"ext_localconf.php";s:4:"b549";s:14:"ext_tables.php";s:4:"eada";s:14:"ext_tables.sql";s:4:"239c";s:24:"ext_typoscript_setup.txt";s:4:"71ba";s:13:"locallang.xml";s:4:"f8ad";s:14:"doc/manual.sxw";s:4:"1e09";s:56:"renderers/class.tx_ddgooglesitemap_abstract_renderer.php";s:4:"0539";s:52:"renderers/class.tx_ddgooglesitemap_news_renderer.php";s:4:"fcb9";s:54:"renderers/class.tx_ddgooglesitemap_normal_renderer.php";s:4:"ec95";s:63:"scheduler/class.tx_ddgooglesitemap_additionalfieldsprovider.php";s:4:"4267";s:48:"scheduler/class.tx_ddgooglesitemap_indextask.php";s:4:"cbaa";}',
	'suggests' => array(
	),
);

?>