<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2014 Dmitry Dulepov <dmitry.dulepov@gmail.com>
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

namespace DmitryDulepov\DdGooglesitemap\Generator;

use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class implements a Google sitemap.
 *
 * @author	Dmitry Dulepov <dmitry.dulepov@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_ddgooglesitemap
 */
class EntryPoint {

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
			GeneralUtility::callUserFunction($userFuncRef, $params, $this);
		}
		else {
			header('HTTP/1.0 400 Bad request', true, 400);
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
		$type = GeneralUtility::_GP('sitemap');
		return ($type ?: self::DEFAULT_SITEMAP_TYPE);
	}

	/**
	 * Initializes TSFE and sets $GLOBALS['TSFE']
	 *
	 * @return	void
	 */
	protected function initTSFE() {
		$GLOBALS['TSFE'] = $tsfe = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController', $GLOBALS['TYPO3_CONF_VARS'], GeneralUtility::_GP('id'), '');
		/** @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $tsfe */
		$tsfe->connectToDB();
		$tsfe->initFEuser();
		\TYPO3\CMS\Frontend\Utility\EidUtility::initTCA();
		$tsfe->determineId();
		$tsfe->initTemplate();
		$tsfe->getConfigArray();

		// Get linkVars, absRefPrefix, etc
		\TYPO3\CMS\Frontend\Page\PageGenerator::pagegenInit();
	}
}

$generator = GeneralUtility::makeInstance('DmitryDulepov\\DdGooglesitemap\\Generator\\EntryPoint');
/* @var EntryPoint $generator */
$generator->main();
