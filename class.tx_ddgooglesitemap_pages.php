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
 * This class produces sitemap for pages
 *
 * @author	Dmitry Dulepov <dmitry.dulepov@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_ddgooglesitemap
 */

class tx_ddgooglesitemap_pages extends tx_ddgooglesitemap_generator {

	/**
	 * List of page uid values to generate entries for
	 *
	 * @var	array
	 */
	protected $pageList = array();

	/**
	 * Number of generated items.
	 *
	 * @var int
	 */
	protected $generatedItemCount = 0;

	/**
	 * A sitemap renderer
	 *
	 * @var	tx_ddgooglesitemap_normal_renderer
	 */
	protected $renderer;

	/**
	 * Hook objects for post-processing
	 *
	 * @var	array
	 */
	protected $hookObjects;

	/**
	 * Initializes the instance of this class. This constructir sets starting
	 * point for the sitemap to the current page id
	 *
	 * @return	void
	 */
	public function __construct() {
		parent::__construct();

		$pid = intval($GLOBALS['TSFE']->tmpl->setup['tx_ddgooglesitemap.']['forceStartPid']);
		if ($pid === 0 || $pid == $GLOBALS['TSFE']->id) {
			$this->pageList[$GLOBALS['TSFE']->id] = array(
				'uid' => $GLOBALS['TSFE']->id,
				'SYS_LASTCHANGED' => $GLOBALS['TSFE']->page['SYS_LASTCHANGED'],
				'tx_ddgooglesitemap_lastmod' => $GLOBALS['TSFE']->page['tx_ddgooglesitemap_lastmod'],
				'tx_ddgooglesitemap_priority' => $GLOBALS['TSFE']->page['tx_ddgooglesitemap_priority'],
				'doktype' => $GLOBALS['TSFE']->page['doktype']
			);
		}
		else {
			$page = $GLOBALS['TSFE']->sys_page->getPage($pid);
			$this->pageList[$page['uid']] = array(
				'uid' => $page['uid'],
				'SYS_LASTCHANGED' => $page['SYS_LASTCHANGED'],
				'tx_ddgooglesitemap_lastmod' => $page['tx_ddgooglesitemap_lastmod'],
				'tx_ddgooglesitemap_priority' => $GLOBALS['TSFE']->page['tx_ddgooglesitemap_priority'],
				'doktype' => $page['doktype']
			);
		}

		$this->renderer = t3lib_div::makeInstance('tx_ddgooglesitemap_normal_renderer');

		// Prepare user defined objects (if any)
		$this->hookObjects = array();
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dd_googlesitemap']['generateSitemapForPagesClass'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dd_googlesitemap']['generateSitemapForPagesClass'] as $classRef) {
				$this->hookObjects[] = t3lib_div::getUserObj($classRef);
			}
		}
	}

	/**
	 * Generates sitemap for pages (<url> entries in the sitemap)
	 *
	 * @return	void
	 */
	protected function generateSitemapContent() {
		// Workaround: we want the sysfolders back into the menu list!
		$GLOBALS['TSFE']->sys_page->where_hid_del = str_replace(
			'pages.doktype<200',
			'pages.doktype<>255',
			$GLOBALS['TSFE']->sys_page->where_hid_del
		);

		while (!empty($this->pageList) && $this->generatedItemCount - $this->offset <= $this->limit) {
			$pageInfo = array_shift($this->pageList);
			if ($this->generatedItemCount >= $this->offset) {
				$this->writeSingleUrl($pageInfo);
			}
			$this->generatedItemCount++;

			// Add subpages of this page to the end of the page list. This way
			// we get top level pages in the sitemap first, then subpages of the
			// first, second, etc pages of the top level pages and so on.
			//
			// Notice: no sorting (for speed)!
			$GLOBALS['TSFE']->sys_page->sys_language_uid = $GLOBALS['TSFE']->config['config']['sys_language_uid'];
			$morePages = $GLOBALS['TSFE']->sys_page->getMenu($pageInfo['uid'],
					'uid,doktype,no_search,l18n_cfg,SYS_LASTCHANGED,tx_ddgooglesitemap_lastmod,tx_ddgooglesitemap_priority',
					'', '', false);

			$this->removePages($morePages);
			$this->pageList = array_merge($this->pageList, array_values($morePages));
			unset($morePages);
		}
	}

	/**
	 * Exclude pages from given list
	 *
	 * @param array $pages
	 * @return void
	 */
	protected function removePages(array &$pages) {
		$language = (int)$GLOBALS['TSFE']->config['config']['sys_language_uid'];
		foreach($pages as $pageUid => $page) {
			// Hide page in default language
			if ($language === 0 && t3lib_div::hideIfDefaultLanguage($page['l18n_cfg'])) {
				unset($pages[$pageUid]);
			// Hide page if no translation is set
			} elseif ($language !== 0 && !isset($page['_PAGES_OVERLAY']) && t3lib_div::hideIfNotTranslated($page['l18n_cfg'])) {
				unset($pages[$pageUid]);
			}
		}
	}

	/**
	 * Outputs information about single page
	 *
	 * @param	array	$pageInfo	Page information (needs 'uid' and 'SYS_LASTCHANGED' columns)
	 * @return	void
	 */
	protected function writeSingleUrl(array $pageInfo) {
		// We ignore non-visible page types!
		if ($pageInfo['doktype'] != 3 &&
			$pageInfo['doktype'] != 4 &&
			$pageInfo['doktype'] != 5 &&
			$pageInfo['doktype'] != 6 &&
			$pageInfo['doktype'] != 7 &&
			$pageInfo['doktype'] != 199 &&
			$pageInfo['doktype'] != 254 &&
			$pageInfo['doktype'] != 255 &&
			$pageInfo['no_search'] == 0 &&
				($url = $this->getPageLink($pageInfo['uid']))) {
			echo $this->renderer->renderEntry($url, $pageInfo['title'],
				$pageInfo['SYS_LASTCHANGED'] > 24*60*60 ? $pageInfo['SYS_LASTCHANGED'] : 0,
				$this->getChangeFrequency($pageInfo), '', $pageInfo['tx_ddgooglesitemap_priority']);

			// Post-process current page and possibly append data
			// @see http://forge.typo3.org/issues/45637
			foreach ($this->hookObjects as $hookObject) {
				if (is_callable(array($hookObject, 'postProcessPageInfo'))) {
					$parameters = array(
						'pageInfo' => &$pageInfo,
						'generatedItemCount' => &$this->generatedItemCount,
						'offset' => $this->offset,
						'limit' => $this->limit,
						'renderer' => $this->renderer,
						'pObj' => $this
					);
					$hookObject->postProcessPageInfo($parameters);
				}
			}
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

/** @noinspection PhpUndefinedVariableInspection */
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap/class.tx_ddgooglesitemap_pages.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap/class.tx_ddgooglesitemap_pages.php']);
}

?>