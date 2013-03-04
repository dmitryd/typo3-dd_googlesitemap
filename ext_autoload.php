<?php
$extpath = t3lib_extMgm::extPath('dd_googlesitemap');
return array(
	'tx_ddgooglesitemap_indextask' => $extpath . 'scheduler/class.tx_ddgooglesitemap_indextask.php',
	'tx_ddgooglesitemap_additionalfieldsprovider' => $extpath . 'scheduler/class.tx_ddgooglesitemap_additionalfieldsprovider.php'
);
