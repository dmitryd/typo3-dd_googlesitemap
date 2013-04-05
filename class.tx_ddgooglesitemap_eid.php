<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2013 Dmitry Dulepov <dmitry.dulepov@gmail.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * This class implements a Google sitemap.
 *
 * @author	Dmitry Dulepov <dmitry.dulepov@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_ddgooglesitemap
 */
class tx_ddgooglesitemap_eid {

	const DEFAULT_SITEMAP_TYPE = 'pages';

	public function __construct() {
		@set_time_limit(300);
		$this->initTSFE();
	}

	/**
	 * Main function of the class. Outputs sitemap.
	 *
	 * @return	void
	 */
	public function main() {
		$sitemapType = $this->getSitemapType();
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dd_googlesitemap']['sitemap'][$sitemapType])) {
			$userFuncRef = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dd_googlesitemap']['sitemap'][$sitemapType];
			$params = array();
			t3lib_div::callUserFunction($userFuncRef, $params, $this);
		}
		else {
			header('400 Bad request');
			header('Content-type: text/plain');
			echo 'No generator found for type \'' . $sitemapType . '\'';
		}
	}

	/**
	 * Determines what sitemap we should send
	 *
	 * @return	string
	 */
	protected function getSitemapType() {
		$type = t3lib_div::_GP('sitemap');
		return ($type ?: self::DEFAULT_SITEMAP_TYPE);
	}

	/**
	 * Initializes TSFE and sets $GLOBALS['TSFE']
	 *
	 * @return	void
	 */
	protected function initTSFE() {
		$GLOBALS['TSFE'] = t3lib_div::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], t3lib_div::_GP('id'), '');
		$GLOBALS['TSFE']->connectToDB();
		$GLOBALS['TSFE']->initFEuser();
		$GLOBALS['TSFE']->determineId();
		if (version_compare(TYPO3_branch, '6.1', '>=')) {
			\TYPO3\CMS\Core\Core\Bootstrap::getInstance()->loadCachedTca();
		}
		else {
			$GLOBALS['TSFE']->getCompressedTCarray();
		}
		$GLOBALS['TSFE']->initTemplate();
		$GLOBALS['TSFE']->getConfigArray();

		// Get linkVars, absRefPrefix, etc
		TSpagegen::pagegenInit();
	}
}

/** @noinspection PhpUndefinedVariableInspection */
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap/class.tx_ddgooglesitemap_eid.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap/class.tx_ddgooglesitemap_eid.php']);
}

$generator = t3lib_div::makeInstance('tx_ddgooglesitemap_eid');
/* @var $generator tx_ddgooglesitemap_eid */
$generator->main();

?>