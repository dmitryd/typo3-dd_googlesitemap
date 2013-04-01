<?php
$extpath = t3lib_extMgm::extPath('dd_googlesitemap');
return array(
	'tx_ddgooglesitemap_indextask' => $extpath . 'scheduler/class.tx_ddgooglesitemap_indextask.php',
	'tx_ddgooglesitemap_additionalfieldsprovider' => $extpath . 'scheduler/class.tx_ddgooglesitemap_additionalfieldsprovider.php',
	'tx_ddgooglesitemap_pages' => $extpath . 'class.tx_ddgooglesitemap_pages.php',
	'tx_ddgooglesitemap_ttnews' => $extpath . 'class.tx_ddgooglesitemap_ttnews.php',
	'tx_ddgooglesitemap_generator' => $extpath . 'class.tx_ddgooglesitemap_generator.php',
	'tx_ddgooglesitemap_normal_renderer' => $extpath . 'renderers/class.tx_ddgooglesitemap_normal_renderer.php',
	'tx_ddgooglesitemap_news_renderer' => $extpath . 'renderes/class.tx_ddgooglesitemap_news_renderer.php',
	'tx_ddgooglesitemap_abstract_renderer' => $extpath . 'renderers/class.tx_ddgooglesitemap_abstract_renderer.php',
);
