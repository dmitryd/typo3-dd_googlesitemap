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
 * $Id$
 */


/**
 * This class implements news sitemap
 * (http://www.google.com/support/webmasters/bin/answer.py?hl=en-nz&answer=42738)
 * for Google.
 *
 * The following URL parameters are expected:
 * - sitemap=news
 * - singlePid=<uid of the "single" tt_news view>
 * - pidList=<comma-separated list of storage pids>
 * All pids must be in the rootline of the current pid. The safest way is to call
 * this site map from the root page of the site:
 * http://example.com/?eID=dd_googlesitemap&sitemap=news&singlePid=100&pidList=101,102,115
 *
 * If you need to show news on different single view pages, make several sitemaps
 * (it is possible with Google).
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_ddgooglesitemap
 */
class tx_ddgooglesitemap_ttnews {

	/**
	 * List of storage pages where news items are located
	 *
	 * @var	array
	 */
	protected $pidList = array();

	/**
	 * cObject to generate links
	 *
	 * @var	tslib_cObj
	 */
	protected $cObj;

	/**
	 * Single view page
	 *
	 * @var	int
	 */
	protected $singlePid;

	/**
	 * Creates an instance of this class
	 *
	 * @return	void
	 */
	public function __construct() {
		$singlePid = intval(t3lib_div::GPvar('singlePid'));
		$this->singlePid = $singlePid && $this->isInRootline($singlePid) ? $singlePid : $GLOBALS['TSFE']->id;

		$this->validateAndcreatePageList();

		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->cObj->start(array());
	}

	/**
	 * Generates news site map.
	 *
	 * @return	void
	 */
	public function main() {
		header('Content-type: text/xml');
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ' .
			'xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . chr(10);
		if (count($this->pidList) > 0) {
			t3lib_div::loadTCA('tt_news');
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,datetime,keywords',
				'tt_news', 'pid IN (' . implode(',', $this->pidList) . ')' .
				$this->cObj->enableFields('tt_news'), '', 'datetime DESC'
			);
			while (false !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
				if (($url = $this->getNewsItemUrl($row['uid']))) {
					echo '<loc>' . $url . '</loc>' . chr(10);
					echo '<news:news>' . chr(10);
					echo '<news:publication_date>' . date('c', $row['datetime']) . '</news:publication_date>' . chr(10);
					if ($row['keywords']) {
						echo '<news:keywords>' . htmlspecialchars($row['keywords']) . '</news:keywords>' . chr(10);
					}
					echo '</news:news>' . chr(10);
				}
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}
		echo '</urlset>';
	}

	/**
	 * Creates a link to the news item
	 *
	 * @param	int	$newsId	News item uid
	 * @return	string
	 */
	protected function getNewsItemUrl($newsId) {
		$conf = array(
			'parameter' => $this->singlePid,
			'additionalParams' => '&tx_ttnews[tt_news]=' . $newsId,
			'returnLast' => 'url',
			'useCacheHash' => true,
		);
		$link = htmlspecialchars($this->cObj->typoLink('', $conf));
		return t3lib_div::locationHeaderUrl($link);
	}

	/**
	 * Checks that page list is in the rootline of the current page and excludes
	 * pages that are outside of the rootline.
	 *
	 * @return	void
	 */
	protected function validateAndcreatePageList() {
		// Get pages
		$pidList = t3lib_div::intExplode(',', t3lib_div::GPvar('pidList'));
		// Check pages
		foreach ($pidList as $pid) {
			if ($this->isInRootline($pid)) {
				$this->pidList[$pid] = $pid;
			}
		}
	}

	/**
	 * Check if supplied page id and current page are in the same root line
	 *
	 * @param	int	$pid	Page id to check
	 * @return	boolean	true if page is in the root line
	 */
	protected function isInRootline($pid) {
		$result = false;
		$rootline = $GLOBALS['TSFE']->sys_page->getRootLine($pid);
		foreach ($rootline as $row) {
			if ($row['uid'] == $GLOBALS['TSFE']->id) {
				$result = true;
				break;
			}
		}
		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap/class.tx_googlesitemap_ttnews.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap/class.tx_googlesitemap_ttnews.php']);
}

?>