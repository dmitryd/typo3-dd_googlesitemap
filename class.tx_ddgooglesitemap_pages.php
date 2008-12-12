<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2008 Dmitry Dulepov <dmitry@typo3.org>
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
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * $Id: $
 */


/**
 * This class produces sitemap for pages
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_ddgooglesitemap
 */

class tx_ddgooglesitemap_pages {

	/**
	 * List of page uid values to generate entries for
	 *
	 * @var	array
	 */
	protected $pageList = array();

	/**
	 * cObject to generate links
	 *
	 * @var	tslib_cObj
	 */
	protected $cObj;

	/**
	 * Initializes the instance of this class. This constructir sets starting
	 * point for the sitemap to the current page id
	 *
	 * @return	void
	 */
	public function __construct() {
		$this->pageList[$GLOBALS['TSFE']->id] = array(
			'uid' => $GLOBALS['TSFE']->id,
			'SYS_LASTCHANGED' => $GLOBALS['TSFE']->page['SYS_LASTCHANGED'],
			'tx_ddgooglesitemap_lastmod' => $GLOBALS['TSFE']->page['tx_ddgooglesitemap_lastmod'],
		);

		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->cObj->start(array());
	}

	/**
	 * Outputs sitemap for pages.
	 *
	 * @return	void
	 */
	public function main() {
		// Start
		header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?>' . chr(10);
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . chr(10);

		// Generate URLs
		$this->generateSitemapForPages();

		// End
		echo '</urlset>';
	}

	/**
	 * Generates sitemap for pages (<url> entries in the sitemap)
	 *
	 * @return	void
	 */
	protected function generateSitemapForPages() {
		while (count($this->pageList) > 0) {
			$pageInfo = array_shift($this->pageList);
			$this->writeSingleUrl($pageInfo);

			// Add subpages of this page to the end of the page list. This way
			// we get top level pages in the sitemap first, then subpages of the
			// first, second, etc pages of the top level pages and so on.
			//
			// Notice: no sorting (for speed)!
			$this->pageList += $GLOBALS['TSFE']->sys_page->getMenu($pageInfo['uid'],
					'uid,SYS_LASTCHANGED,tx_ddgooglesitemap_lastmod', '');
		}
	}

	/**
	 * Outputs information about single page
	 *
	 * @param	array	$pageInfo	Page information (needs 'uid' and 'SYS_LASTCHANGED' columns
	 * @return	void
	 */
	protected function writeSingleUrl(array $pageInfo) {
		if (($url = $this->getPageLink($pageInfo['uid']))) {
			echo '<url>' . chr(10);
			echo '<loc>' . $url . '</loc>' . chr(10);
			if ($pageInfo['SYS_LASTCHANGED'] > 24*60*60) {
				echo '<lastmod>' . date('c', $pageInfo['SYS_LASTCHANGED']) . '</lastmod>' . chr(10);
			}
			echo '<changefreq>' . $this->getChangeFrequency($pageInfo) . '</changefreq>' . chr(10);
			//echo '<priority>0.8</priority>' . chr(10);
			echo '</url>' . chr(10);
		}
	}

	protected function getChangeFrequency(array $pageInfo) {
		$timeValues = t3lib_div::intExplode(',', $pageInfo['tx_ddgooglesitemap_lastmod']);
		// Remove zeros
		foreach ($timeValues as $k => $v) {
			if ($v == 0) {
				unset($timeValues[$k]);
			}
		}
		$timeValues[] = $pageInfo['SYS_LASTCHANGED'];
		$timeValues[] = time();
		sort($timeValues, SORT_NUMERIC);
		$sum = 0;
		for ($i = count($timeValues) - 1; $i > 0; $i--) {
			$sum += ($timeValues[$i] - $timeValues[$i - 1]);
		}
		$average = ($sum/(count($timeValues) - 1));
		return ($average >= 180*24*60*60 ? 'yearly' :
				($average <= 24*60*60 ? 'daily' :
				($average <= 60*60 ? 'hourly' :
				($average <= 14*24*60*60 ? 'weekly' : 'monthly'))));
	}

	/**
	 * Creates a link to a single page
	 *
	 * @param	array	$pageId	Page ID
	 * @return	string	Full URL of the page including host name (escaped)
	 */
	protected function getPageLink($pageId) {
		$conf = array(
			'parameter' => $pageId,
			'returnLast' => 'url',
		);
		$link = htmlspecialchars($this->cObj->typoLink('', $conf));
		return t3lib_div::locationHeaderUrl($link);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap/class.tx_ddgooglesitemap_pages.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap/class.tx_ddgooglesitemap_pages.php']);
}

?>