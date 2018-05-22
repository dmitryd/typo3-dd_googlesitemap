<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2014 Dmitry Dulepov <dmitry.dulepov@gmail.com>
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

namespace DmitryDulepov\DdGooglesitemap\Helper;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SysLanguageHelper
 *
 * @package DmitryDulepov\DdGooglesitemap\Helper
 */
class SysLanguageHelper implements SingletonInterface {

	/**
	 * @var array of sys languages [uid => languageCode]
	 */
	protected $sysLanguages = null;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @return \TYPO3\CMS\Frontend\Page\PageRepository
	 * @throws \InvalidArgumentException
	 */
	protected function getPageRepository() {
		if(TYPO3_MODE === 'BE') {
			\TYPO3\CMS\Frontend\Utility\EidUtility::initTCA();
			if (!is_object($GLOBALS['TT'])) {
				$GLOBALS['TT'] = new \TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
				$GLOBALS['TT']->start();
			}

			$GLOBALS['TSFE'] = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController', $GLOBALS['TYPO3_CONF_VARS'], GeneralUtility::_GP('id'), '');
			$GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
			$GLOBALS['TSFE']->sys_page->init(TRUE);
			$GLOBALS['TSFE']->connectToDB();
			$GLOBALS['TSFE']->initFEuser();
			$GLOBALS['TSFE']->determineId();
			$GLOBALS['TSFE']->initTemplate();
			$GLOBALS['TSFE']->getConfigArray();
		}

		return $GLOBALS['TSFE']->sys_page;
	}

	/**
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public function getSysLanguages() {
		if ($this->sysLanguages === null) {
			$this->sysLanguages = array();
			$sys_languages      = $this->getPageRepository()->getRecordsByField('sys_language', 'hidden', 0);

			// empty table
			if (!is_array($sys_languages)) {
				$sys_languages = array();
			}

			// default language not in table, so add manually
			array_unshift($sys_languages, array('language_isocode' => 'x-default', 'uid' => 0));

			foreach ($sys_languages as $language) {
				// use iso code as default
				$setLocale = $language['language_isocode'];

				// check typoscript config
				/** @var \TYPO3\CMS\Core\TypoScript\TemplateService $templateService */
				$templateService = GeneralUtility::makeInstance(
					'TYPO3\\CMS\\Core\\TypoScript\\TemplateService'
				);
				$templateService->matchAlternative[] = '[globalVar = GP:L = ' . $language['uid'] . ']';
				$templateService->init();
				if(TYPO3_MODE === 'FE') {
					$templateService->start($GLOBALS['TSFE']->rootLine);
				}
				// apply language condition
				$templateService->generateConfig();

				// allow custom modifications. We not change TSFE->config array and this could be important.
				// Add possibility, that extension could add their modification on setup-array
				if (is_array(
					$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['dd_google_sitemap/sys_language_helper']['alternateSysLanguageIdsPostProc']
				)) {
					$params = array('templateService' => $templateService);
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['dd_google_sitemap/sys_language_helper']['alternateSysLanguageIdsPostProc'] as $funcRef) {
						GeneralUtility::callUserFunction($funcRef, $params, $this);
					}
				}

				if (isset($templateService->setup['config.']['locale_all'])) {
					// could contain charset
					list($locale_all) = explode('.', $templateService->setup['config.']['locale_all'], 2);

					if (\strpos($locale_all, '_') === 2) {
						list($lang, $region) = explode('_', $locale_all, 3);
						$setLocale = strtolower($lang . '-' . $region);
					} else {
						$setLocale = $locale_all;
					}
				} elseif (isset($templateService->setup['config.']['language'])) {
					$setLocale = $templateService->setup['config.']['language'];
				}

				$this->sysLanguages[(int)$language['uid']] = $setLocale;
			}

		}

		return $this->sysLanguages;
	}
}
