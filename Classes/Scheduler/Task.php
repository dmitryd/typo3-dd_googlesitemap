<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Dmitry Dulepov <dmitry.dulepov@gmail.com>
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

namespace DmitryDulepov\DdGooglesitemap\Scheduler;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class provides a scheduler task to create sitemap index as required
 * by the Google sitemap protocol.
 *
 * @author Dmitry Dulepov <dmitry.dulepov@gmail.com>
 * @see http://support.google.com/webmasters/bin/answer.py?hl=en&answer=71453
 */
class Task extends \TYPO3\CMS\Scheduler\Task\AbstractTask {

	const DEFAULT_FILE_PATH = 'typo3temp/dd_googlesitemap';

	/** @var string */
	private $baseUrl;

	/** @var string */
	protected $eIdScriptUrl;

	/** @var string */
	protected $indexFilePath;

	/** @var int */
	protected $maxUrlsPerSitemap = 50000;

	/** @var string */
	private $sitemapFileFormat;

	/** @var int */
	private $offset;

	/**
	 * Creates the instance of the class. This call initializes the index file
	 * path to the random value. After the task is configured, the user may
	 * change the file and the file name will be serialized with the task and
	 * used later.
	 *
	 * @see __sleep
	 */
	public function __construct() {
		parent::__construct();
		$this->indexFilePath = self::DEFAULT_FILE_PATH . '/' . GeneralUtility::getRandomHexString(24) . '.xml';
	}

	/**
	 * Reconstructs some variables after the object is unserialized.
	 *
	 * @return void
	 */
	public function __wakeup() {
		$this->buildSitemapFileFormat();
		$this->buildBaseUrl();
	}

	/**
	 * This is the main method that is called when a task is executed
	 * It MUST be implemented by all classes inheriting from this one
	 * Note that there is no error handling, errors and failures are expected
	 * to be handled and logged by the client implementations.
	 * Should return true on successful execution, false on error.
	 *
	 * @return boolean    Returns true on successful execution, false on error
	 */
	public function execute() {
		$indexFilePathTemp = PATH_site . $this->indexFilePath . '.tmp';
		$indexFile = fopen($indexFilePathTemp, 'wt');
		fwrite($indexFile, '<?xml version="1.0" encoding="UTF-8"?>' . chr(10));
		fwrite($indexFile, '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . chr(10));

		$eIDscripts = GeneralUtility::trimExplode(chr(10), $this->eIdScriptUrl);
		$eIdIndex = 1;
		foreach ($eIDscripts as $eIdScriptUrl) {
			$this->offset = 0;
			$currentFileNumber = 1;
			$lastFileHash = '';
			do {
				$sitemapFileName = sprintf($this->sitemapFileFormat, $eIdIndex, $currentFileNumber++);
				$this->buildSitemap($eIdScriptUrl, $sitemapFileName);

				$isSitemapEmpty = $this->isSitemapEmpty($sitemapFileName);
				$currentFileHash = $isSitemapEmpty ? -1 : md5_file(PATH_site . $sitemapFileName);
				$stopLoop = $isSitemapEmpty || ($currentFileHash == $lastFileHash);

				if ($stopLoop) {
					@unlink(PATH_site . $sitemapFileName);
				}
				else {
					fwrite($indexFile, '<sitemap><loc>' . htmlspecialchars($this->makeSitemapUrl($sitemapFileName)) . '</loc></sitemap>' . chr(10));
					$lastFileHash = $currentFileHash;
				}
			} while (!$stopLoop);
			$eIdIndex++;
		}

		fwrite($indexFile, '</sitemapindex>' . chr(10));
		fclose($indexFile);

		@unlink(PATH_site . $this->indexFilePath);
		rename($indexFilePathTemp, PATH_site . $this->indexFilePath);

		return true;
	}

	/**
	 * This method is designed to return some additional information about the task,
	 * that may help to set it apart from other tasks from the same class
	 * This additional information is used - for example - in the Scheduler's BE module
	 * This method should be implemented in most task classes
	 *
	 * @return	string	Information to display
	 */
	public function getAdditionalInformation() {
		/** @noinspection PhpUndefinedMethodInspection */
		$format = $GLOBALS['LANG']->sL('LLL:EXT:dd_googlesitemap/locallang.xml:scheduler.extra_info');
		return sprintf($format, $this->getIndexFileUrl());
	}

	/**
	 * Sets the url of the eID script. This is called from the task
	 * configuration inside scheduler.
	 *
	 * @return string
	 * @see tx_ddgooglesitemap_additionalfieldsprovider
	 */
	public function getEIdScriptUrl() {
		return $this->eIdScriptUrl;
	}

	/**
	 * Returns the index file path. This is called from the task
	 * configuration inside scheduler.
	 *
	 * @return string
	 * @see tx_ddgooglesitemap_additionalfieldsprovider
	 */
	public function getIndexFilePath() {
		return $this->indexFilePath;
	}

	/**
	 * Obtains the number of urls per sitemap. This is called from the task
	 * configuration inside scheduler.
	 *
	 * @return int
	 * @see tx_ddgooglesitemap_additionalfieldsprovider
	 */
	public function getMaxUrlsPerSitemap() {
		return $this->maxUrlsPerSitemap;
	}

	/**
	 * Sets the URl of the eID script. This is called from the task
	 * configuration inside scheduler.
	 *
	 * @param $url
	 * @see tx_ddgooglesitemap_additionalfieldsprovider
	 */
	public function setEIdScriptUrl($url) {
		$this->eIdScriptUrl = $url;
	}

	/**
	 * Sets the URL of the eID script. This is called from the task
	 * configuration inside scheduler.
	 *
	 * @param string $path
	 * @see tx_ddgooglesitemap_additionalfieldsprovider
	 */
	public function setIndexFilePath($path) {
		$this->indexFilePath = $path;
	}

	/**
	 * Sets the number of URLs per sitemap. This is called from the task
	 * configuration inside scheduler.
	 *
	 * @param int $maxUrlsPerSitemap
	 * @see tx_ddgooglesitemap_additionalfieldsprovider
	 */
	public function setMaxUrlsPerSitemap($maxUrlsPerSitemap) {
		$this->maxUrlsPerSitemap = $maxUrlsPerSitemap;
	}

	/**
	 * Creates a base url for sitemaps.
	 *
	 * @return void
	 */
	protected function buildBaseUrl() {
		$urlParts = parse_url($this->eIdScriptUrl);
		$this->baseUrl = $urlParts['scheme'] . '://';
		if ($urlParts['user']) {
			$this->baseUrl .= $urlParts['user'];
			if ($urlParts['pass']) {
				$this->baseUrl .= ':' . $urlParts['pass'];
			}
			$this->baseUrl .= '@';
		}
		$this->baseUrl .= $urlParts['host'];
		if ($urlParts['port']) {
			$this->baseUrl .= ':' . $urlParts['port'];
		}
		$this->baseUrl .= '/';
	}

	/**
	 * Builds the sitemap.
	 *
	 * @param string $eIdScriptUrl
	 * @param string $sitemapFileName
	 * @see tx_ddgooglesitemap_additionalfieldsprovider
	 */
	protected function buildSitemap($eIdScriptUrl, $sitemapFileName) {
		$url = $eIdScriptUrl . sprintf('&offset=%d&limit=%d', $this->offset, $this->maxUrlsPerSitemap);

		$content = GeneralUtility::getURL($url);
		if ($content) {
			file_put_contents(PATH_site . $sitemapFileName, $content);
			$this->offset += $this->maxUrlsPerSitemap;
		}
	}

	/**
	 * Creates the format string for the sitemap files.
	 *
	 * @return void
	 */
	protected function buildSitemapFileFormat() {
		$fileParts = pathinfo($this->indexFilePath);
		$this->sitemapFileFormat = $fileParts['dirname'] . '/' . $fileParts['filename'] . '_sitemap_%05d_%05d.xml';
	}

	/**
	 * Returns the index file url.
	 *
	 * @return string
	 */
	protected function getIndexFileUrl() {
		return $this->baseUrl . $this->indexFilePath;
	}

	/**
	 * Checks if the current sitemap has no entries. The function reads a chunk
	 * of the file, which is large enough to have a '<url>' token in it and
	 * examines the chunk. If the token is not found, than the sitemap is either
	 * empty or corrupt.
	 *
	 * @param string $sitemapFileName
	 * @return bool
	 */
	protected function isSitemapEmpty($sitemapFileName) {
		$result = TRUE;

		$fileDescriptor = @fopen(PATH_site . $sitemapFileName, 'rt');
		if ($fileDescriptor) {
			$chunkSizeToCheck = 10240;
			$testString = fread($fileDescriptor, $chunkSizeToCheck);
			fclose($fileDescriptor);
			$result = (strpos($testString, '<url>') === FALSE);
		}

		return $result;
	}

	/**
	 * Creates a url to the sitemap.
	 *
	 * @param string $siteMapPath
	 * @return string
	 */
	protected function makeSitemapUrl($siteMapPath) {
		return $this->baseUrl . $siteMapPath;
	}
}
