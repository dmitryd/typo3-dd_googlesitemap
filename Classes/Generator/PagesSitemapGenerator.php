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

use DmitryDulepov\DdGooglesitemap\Renderers\AbstractExtendedSitemapRenderer;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * This class produces sitemap for pages
 *
 * @author	Dmitry Dulepov <dmitry.dulepov@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_ddgooglesitemap
 */

class PagesSitemapGenerator extends AbstractSitemapGenerator {

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
	 * @var AbstractExtendedSitemapRenderer
	 */
	protected $renderer;

	/** @var array */
	protected $excludedPageTypes = array(0, 3, 4, 5, 6, 7, 199, 254, 255);

	/**
	 * Hook objects for post-processing
	 *
	 * @var	array
	 */
	protected $hookObjects;

	/**
	 * Initializes the instance of this class. This constructor sets starting
	 * point for the sitemap to the current page id
	 */
	public function __construct() {
		parent::__construct();

		$excludePageTypes = GeneralUtility::intExplode(',', $GLOBALS['TSFE']->tmpl->setup['tx_ddgooglesitemap.']['excludePageType'], TRUE);
		if (count($excludePageTypes) > 0) {
			$this->excludedPageTypes = $excludePageTypes;
		}

		$pid = intval($GLOBALS['TSFE']->tmpl->setup['tx_ddgooglesitemap.']['forceStartPid']);
		if ($pid === 0 || $pid == $GLOBALS['TSFE']->id) {
			$this->pageList[$GLOBALS['TSFE']->id] = array(
				'uid' => $GLOBALS['TSFE']->id,
				'SYS_LASTCHANGED' => $GLOBALS['TSFE']->page['SYS_LASTCHANGED'],
				'tx_ddgooglesitemap_lastmod' => $GLOBALS['TSFE']->page['tx_ddgooglesitemap_lastmod'],
				'tx_ddgooglesitemap_priority' => $GLOBALS['TSFE']->page['tx_ddgooglesitemap_priority'],
				'tx_ddgooglesitemap_change_frequency' => $GLOBALS['TSFE']->page['tx_ddgooglesitemap_change_frequency'],
				'doktype' => $GLOBALS['TSFE']->page['doktype'],
				'no_search' => $GLOBALS['TSFE']->page['no_search']
			);
		}
		else {
			$page = $GLOBALS['TSFE']->sys_page->getPage($pid);
			$this->pageList[$page['uid']] = array(
				'uid' => $page['uid'],
				'SYS_LASTCHANGED' => $page['SYS_LASTCHANGED'],
				'tx_ddgooglesitemap_lastmod' => $page['tx_ddgooglesitemap_lastmod'],
				'tx_ddgooglesitemap_priority' => $page['tx_ddgooglesitemap_priority'],
				'tx_ddgooglesitemap_change_frequency' => $GLOBALS['TSFE']->page['tx_ddgooglesitemap_change_frequency'],
				'doktype' => $page['doktype'],
				'no_search' => $page['no_search']
			);
		}

		$this->renderer = GeneralUtility::makeInstance('DmitryDulepov\\DdGooglesitemap\\Renderers\\StandardSitemapRenderer');

		// Prepare user defined objects (if any)
		$this->hookObjects = array();
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dd_googlesitemap']['generateSitemapForPagesClass'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dd_googlesitemap']['generateSitemapForPagesClass'] as $classRef) {
				$this->hookObjects[] = GeneralUtility::getUserObj($classRef);
			}
		}
	}

	/**
	 * Generates sitemap for pages (<url> entries in the sitemap)
	 *
	 * @return    void
	 * @throws \InvalidArgumentException
	 */
	protected function generateSitemapContent() {
		// Workaround: we want the sysfolders back into the menu list!
		// We also exclude "Backend user section" pages.
		$useDbalSyntax = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_branch) >= 8004000;
		if ($useDbalSyntax) {
			$GLOBALS['TSFE']->sys_page->where_hid_del = str_replace(
				'`pages`.`doktype` < 200',
				'`pages`.`doktype` <> 255 AND `pages`.`doktype` <> 6',
				$GLOBALS['TSFE']->sys_page->where_hid_del
			);
		} else {
			$GLOBALS['TSFE']->sys_page->where_hid_del = str_replace(
				'pages.doktype<200',
				'pages.doktype<>255 AND pages.doktype<>6',
				$GLOBALS['TSFE']->sys_page->where_hid_del
			);
		}

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
			$morePages = $GLOBALS['TSFE']->sys_page->getMenu($pageInfo['uid'], '*', '', '', false);
			$morePages = $this->filterNonTranslatedPages($morePages);
			$this->pageList = array_merge($this->pageList, array_values($morePages));
			unset($morePages);
		}
	}

	/**
	 * Obtains the last modification date of the page.
	 *
	 * @param array $pageInfo
	 * @return int
	 */
	protected function getLastMod(array $pageInfo) {
		$lastModDates = GeneralUtility::intExplode(',', $pageInfo['tx_ddgooglesitemap_lastmod']);
		$lastModDates[] = intval($pageInfo['SYS_LASTCHANGED']);
		rsort($lastModDates, SORT_NUMERIC);
		reset($lastModDates);

		return current($lastModDates);
	}

	/**
	 * Exclude pages from given list
	 *
	 * @deprecated use filterNonTranslatedPages
	 *
	 * @param array $pages
	 * @return void
	 */
	protected function removeNonTranslatedPages(array &$pages) {
		$pages = $this->filterNonTranslatedPages($pages);
	}

	/**
	 * Get only translated pages
	 *
	 * @param array    $pages
	 * @param int|null $languageUid
	 * @return array
	 */
	protected function filterNonTranslatedPages(array $pages, $languageUid = null) {
		if($languageUid === null) {
			$languageUid = (int)$GLOBALS['TSFE']->config['config']['sys_language_uid'];
		}

		$filterFunction = function ($page) use($languageUid) {
			if ($languageUid === 0 && GeneralUtility::hideIfDefaultLanguage($page['l18n_cfg'])) {
				return false;
			}

			if ($languageUid !== 0 && !isset($page['_PAGES_OVERLAY']) && GeneralUtility::hideIfNotTranslated($page['l18n_cfg'])) {
				return false;
			}

			return true;
		};

		// requested language === current language
		if ($languageUid === (int)$GLOBALS['TSFE']->config['config']['sys_language_uid']) {
			return array_filter($pages, $filterFunction);
		}

		// other language: load overlay information
		$overlayPages = $GLOBALS['TSFE']->sys_page->getPagesOverlay($pages, $languageUid);
		$overlayPages = array_filter($overlayPages, $filterFunction);

		return array_intersect_key($pages, $overlayPages);
	}

	/**
	 * Checks if the page should be included into the sitemap.
	 *
	 * @param array $pageInfo
	 * @return bool
	 */
	protected function shouldIncludePageInSitemap(array $pageInfo) {
		return !$pageInfo['no_search'] && !in_array($pageInfo['doktype'], $this->excludedPageTypes);
	}

	/**
	 * Outputs information about single page
	 *
	 * @param    array $pageInfo Page information (needs 'uid' and 'SYS_LASTCHANGED' columns)
	 * @return    void
	 * @throws \InvalidArgumentException
	 */
	protected function writeSingleUrl(array $pageInfo) {
		if ($this->shouldIncludePageInSitemap($pageInfo) && ($url = $this->getPageLink($pageInfo['uid']))) {
			echo $this->renderer->renderEntry($url, $pageInfo['title'],
				$this->getLastMod($pageInfo),
				$this->getChangeFrequency($pageInfo), '', $pageInfo['tx_ddgooglesitemap_priority'],
				array(
					'hreflangs' => $this->getAlternateLinks($pageInfo)
				));

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
					/** @noinspection PhpUndefinedMethodInspection */
					$hookObject->postProcessPageInfo($parameters);
				}
			}
		}
	}

	/**
	 * Fetches change frequency value.
	 *
	 * @param array $pageInfo
	 * @return string
	 */
	protected function getChangeFrequency(array $pageInfo) {
		if ($pageInfo['tx_ddgooglesitemap_change_frequency']) {
			$changeFrequency = $pageInfo['tx_ddgooglesitemap_change_frequency'];
		} else {
			$changeFrequency = $this->calculateChangeFrequency($pageInfo);
		}

		return $changeFrequency;
	}

	/**
	 * Calculates change frequency.
	 *
	 * @param array $pageInfo
	 * @return string
	 */
	protected function calculateChangeFrequency(array $pageInfo) {
		$timeValues = GeneralUtility::intExplode(',', $pageInfo['tx_ddgooglesitemap_lastmod']);
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
	 * @param array $pageInfo
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	protected function getAlternateLinks($pageInfo) {
		$links                = array();
		$pageUid              = $pageInfo['uid'];
		$alternativeLanguages = $this->getAlternateSysLanguageIds();
		if (!empty($alternativeLanguages)) {
			foreach ($alternativeLanguages as $languageUid => $locale) {
				$translatedPage = $this->filterNonTranslatedPages(array($pageInfo), $languageUid);
				if(empty($translatedPage)) {
					continue;
				}

				// can generate url and it different to target url
				if (($url = $this->getPageLink($pageUid, $languageUid))) {
					$links[$locale] = $url;
				}
			}
		}

		return $links;
	}

	/**
	 * Creates a link to a single page
	 *
	 * @param	int	$pageId	Page ID
	 * @param	int	$languageId	Language Id
	 * @return	string	Full URL of the page including host name (escaped)
	 */
	protected function getPageLink($pageId, $languageId = null) {
		$conf = array(
			'parameter' => $pageId,
			'returnLast' => 'url',
			'forceAbsoluteUrl' => 1
		);

		if ($languageId !== null) {
			$conf['additionalParams'] = '&L=' . $languageId;
			// cHash is important for e.g. realUrl
			$conf['useCacheHash'] = true;
		}

		return htmlspecialchars($this->cObj->typoLink('', $conf));
	}
}
