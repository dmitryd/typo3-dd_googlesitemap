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

require_once(t3lib_extMgm::extPath('dd_googlesitemap', 'renderers/class.tx_ddgooglesitemap_normal_renderer.php'));
require_once(t3lib_extMgm::extPath('dd_googlesitemap', 'renderers/class.tx_ddgooglesitemap_news_renderer.php'));

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
	 * Indicates sitemap type
	 *
	 * @var boolean
	 */
	protected $isNewsSitemap;

	/**
	 * Single view page
	 *
	 * @var	int
	 */
	protected $singlePid;

	/**
	 * A sitemap rendere
	 *
	 * @var	tx_ddgooglesitemap_abstract_renderer
	 */
	protected $renderer;

	/**
	 * Creates an instance of this class
	 *
	 * @return	void
	 */
	public function __construct() {
		$singlePid = intval(t3lib_div::_GP('singlePid'));
		$this->singlePid = $singlePid && $this->isInRootline($singlePid) ? $singlePid : $GLOBALS['TSFE']->id;

		$this->validateAndcreatePageList();

		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->cObj->start(array());

		// Determine renderer type for news
		$this->isNewsSitemap = (t3lib_div::_GET('type') === 'news');
		$rendererClass = ($this->isNewsSitemap ?
			'tx_ddgooglesitemap_news_renderer' : 'tx_ddgooglesitemap_normal_renderer');
		$this->renderer = t3lib_div::makeInstance($rendererClass);
	}

	/**
	 * Generates news site map.
	 *
	 * @return	void
	 */
	public function main() {
		header('Content-type: text/xml');
		echo $this->renderer->getStartTags();

		if (count($this->pidList) > 0) {
			t3lib_div::loadTCA('tt_news');
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,datetime,keywords',
				'tt_news', 'pid IN (' . implode(',', $this->pidList) . ')' .
				($this->isNewsSitemap ? ' AND crdate>=' . (time() - 48*60*60) : '') .
				$this->cObj->enableFields('tt_news'), '', 'datetime DESC'
			);
			$rowCount = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
			while (false !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
				if (($url = $this->getNewsItemUrl($row['uid']))) {
					echo $this->renderer->renderEntry($url, $row['title'], $row['datetime'],
						'', $row['keywords']);
				}
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);

			if ($rowCount === 0) {
				echo '<!-- It appears that there are no tt_news entries. If your ' .
					'news storage sysfolder is outside of the rootline, you may ' .
					'want to use the dd_googlesitemap.skipRootlineCheck=1 TS ' .
					'setup option. Beware: it is insecure and may cause certain ' .
					'undesired effects! Better move your news sysfolder ' .
					'inside the rootline! -->';
			}
		}

		echo $this->renderer->getEndTags();
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
		$pidList = t3lib_div::intExplode(',', t3lib_div::_GP('pidList'));
		// Check pages
		foreach ($pidList as $pid) {
			if ($pid && $this->isInRootline($pid)) {
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
		if (isset($GLOBALS['TSFE']->config['config']['tx_ddgooglesitemap_skipRootlineCheck'])) {
			$skipRootlineCheck = $GLOBALS['TSFE']->config['config']['tx_ddgooglesitemap_skipRootlineCheck'];
		}
		else {
			$skipRootlineCheck = $GLOBALS['TSFE']->tmpl->setup['tx_ddgooglesitemap.']['skipRootlineCheck'];
		}
		if ($skipRootlineCheck) {
			$result = true;
		}
		else {
			$result = false;
			$rootPid = intval($GLOBALS['TSFE']->tmpl->setup['tx_ddgooglesitemap.']['forceStartPid']);
			if ($rootPid == 0) {
				$rootPid = $GLOBALS['TSFE']->id;
			}
			$rootline = $GLOBALS['TSFE']->sys_page->getRootLine($pid);
			foreach ($rootline as $row) {
				if ($row['uid'] == $rootPid) {
					$result = true;
					break;
				}
			}
		}
		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap/class.tx_googlesitemap_ttnews.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap/class.tx_googlesitemap_ttnews.php']);
}

?>