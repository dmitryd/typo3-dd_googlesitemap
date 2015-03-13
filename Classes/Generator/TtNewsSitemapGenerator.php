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
use \TYPO3\CMS\Core\Utility\MathUtility;

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
 * @author	Dmitry Dulepov <dmitry.dulepov@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_ddgooglesitemap
 */
class TtNewsSitemapGenerator extends AbstractSitemapGenerator {

	/**
	 * List of storage pages where news items are located
	 *
	 * @var	array
	 */
	protected $pidList = array();

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
	 * If true, try to get the single pid for a news item from its (first) category with fallback to $this->singlePid
	 *
	 * @var boolean
	 */
	protected $useCategorySinglePid;

	/**
	 * Creates an instance of this class
	 */
	public function __construct() {
		$this->isNewsSitemap = (GeneralUtility::_GET('type') === 'news');
		if ($this->isNewsSitemap) {
			$this->rendererClass = 'DmitryDulepov\\DdGooglesitemap\\Renderers\\NewsSitemapRenderer';
		}

		parent::__construct();

		$singlePid = intval(GeneralUtility::_GP('singlePid'));
		$this->singlePid = $singlePid && $this->isInRootline($singlePid) ? $singlePid : $GLOBALS['TSFE']->id;
		$this->useCategorySinglePid = (bool) GeneralUtility::_GP('useCategorySinglePid');

		$this->validateAndcreatePageList();
	}

	/**
	 * Generates news site map.
	 *
	 * @return	void
	 */
	protected function generateSitemapContent() {
		if (count($this->pidList) > 0) {
			$languageCondition = '';
			$language = GeneralUtility::_GP('L');
			if (MathUtility::canBeInterpretedAsInteger($language)) {
				$languageCondition = ' AND sys_language_uid=' . $language;
			}

			/** @noinspection PhpUndefinedMethodInspection */
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',
				'tt_news', 'pid IN (' . implode(',', $this->pidList) . ')' .
				($this->isNewsSitemap ? ' AND crdate>=' . (time() - 48*60*60) : '') .
				$languageCondition .
				$this->cObj->enableFields('tt_news'), '', 'datetime DESC',
				$this->offset . ',' . $this->limit
			);
			/** @noinspection PhpUndefinedMethodInspection */
			$rowCount = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
			/** @noinspection PhpUndefinedMethodInspection */
			while (false !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
				$forceSinglePid = NULL;
				if ($row['category'] && $this->useCategorySinglePid) {
					$forceSinglePid = $this->getSinglePidFromCategory($row['uid']);
				}
				if (($url = $this->getNewsItemUrl($row, $forceSinglePid))) {
					echo $this->renderer->renderEntry($url, $row['title'], $row['datetime'],
						'', $row['keywords']);
				}
			}
			/** @noinspection PhpUndefinedMethodInspection */
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
	}

	/**
	 * Obtains a pid for the single view from the category.
	 *
	 * @param int $newsId
	 * @return int|null
	 */
	protected function getSinglePidFromCategory($newsId) {
		/** @noinspection PhpUndefinedMethodInspection */
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'tt_news_cat.single_pid',
			'tt_news',
			'tt_news_cat_mm',
			'tt_news_cat',
			' AND tt_news_cat_mm.uid_local = ' . intval($newsId)
		);
		/** @noinspection PhpUndefinedMethodInspection */
		$categoryRecord = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

		return $categoryRecord['single_pid'] ?: NULL;
	}

	/**
	 * Creates a link to the news item
	 *
	 * @param array	$newsRow News item
	 * @param	int	$forceSinglePid Single View page for this news item
	 * @return	string
	 */
	protected function getNewsItemUrl($newsRow, $forceSinglePid = NULL) {
		$link = '';
		if (is_string($GLOBALS['TSFE']->tmpl->setup['tx_ddgooglesitemap.']['newsLink']) && is_array($GLOBALS['TSFE']->tmpl->setup['tx_ddgooglesitemap.']['newsLink'])) {
			$cObj = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
			/** @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj */
			$cObj->start($newsRow, 'tt_news');
			$cObj->setCurrentVal($forceSinglePid ?: $this->singlePid);
			$link = $cObj->cObjGetSingle($GLOBALS['TSFE']->tmpl->setup['tx_ddgooglesitemap.']['newsLink'], $GLOBALS['TSFE']->tmpl->setup['tx_ddgooglesitemap.']['newsLink']);
			unset($cObj);
		}
		if ($link == '') {
			$conf = array(
				'additionalParams' => '&tx_ttnews[tt_news]=' . $newsRow['uid'],
				'forceAbsoluteUrl' => 1,
				'parameter' => $forceSinglePid ?: $this->singlePid,
				'returnLast' => 'url',
				'useCacheHash' => true,
			);
			$link = htmlspecialchars($this->cObj->typoLink('', $conf));
		}
		return $link;
	}

	/**
	 * Checks that page list is in the rootline of the current page and excludes
	 * pages that are outside of the rootline.
	 *
	 * @return	void
	 */
	protected function validateAndcreatePageList() {
		// Get pages
		$pidList = GeneralUtility::intExplode(',', GeneralUtility::_GP('pidList'));
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
