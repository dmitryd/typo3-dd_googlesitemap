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

/**
 * This class provides information about additional fields for the scheduler
 * task of this extension. Additional fields are:
 * - the URL of the eID script (users can use different parameters for the script!)
 * - index file path (users will submit that to Google)
 * - maximum number of URLs in the sitemap (to prevent out of memory errors)
 *
 * @author Dmitry Dulepov <dmitry.dulepov@gmail.com>
 */
class tx_ddgooglesitemap_additionalfieldsprovider implements tx_scheduler_AdditionalFieldProvider {

	/**
	 * Gets additional fields to render in the form to add/edit a task
	 *
	 * @param    array                    Values of the fields from the add/edit task form
	 * @param    tx_ddgooglesitemap_indextask        The task object being eddited. Null when adding a task!
	 * @param    tx_scheduler_Module        Reference to the scheduler backend module
	 * @return    array                    A two dimensional array, array('Identifier' => array('fieldId' => array('code' => '', 'label' => '', 'cshKey' => '', 'cshLabel' => ''))
	 */
	public function getAdditionalFields(array &$taskInfo, $task, tx_scheduler_Module $schedulerModule) {
		$additionalFields = array();

		if ($schedulerModule->CMD == 'add') {
			$url = t3lib_div::locationHeaderUrl('/index.php?eID=dd_googlesitemap');
			$task = t3lib_div::makeInstance('tx_ddgooglesitemap_indextask');
		}
		else {
			$url = $task->getEIdScriptUrl();
		}
		$indexFilePath = $task->getIndexFilePath();
		$maxUrlsPerSitemap = $task->getMaxUrlsPerSitemap();

		$additionalFields['eIdUrl'] = array(
			'code'     => '<textarea style="width:350px;height:200px" name="tx_scheduler[eIdUrl]" wrap="off">' . htmlspecialchars($url) . '</textarea>',
			'label'    => 'LLL:EXT:dd_googlesitemap/locallang.xml:scheduler.eIDFieldLabel',
			'cshKey'   => '',
			'cshLabel' => ''
		);
		$additionalFields['indexFilePath'] = array(
			'code'     => '<input class="wide" type="text" name="tx_scheduler[indexFilePath]" value="' . htmlspecialchars($indexFilePath) . '" />',
			'label'    => 'LLL:EXT:dd_googlesitemap/locallang.xml:scheduler.indexFieldLabel',
			'cshKey'   => '',
			'cshLabel' => ''
		);
		$additionalFields['maxUrlsPerSitemap'] = array(
			'code'     => '<input type="text" name="tx_scheduler[maxUrlsPerSitemap]" value="' . $maxUrlsPerSitemap . '" />',
			'label'    => 'LLL:EXT:dd_googlesitemap/locallang.xml:scheduler.maxUrlsPerSitemapLabel',
			'cshKey'   => '',
			'cshLabel' => ''
		);

		return $additionalFields;
	}

	/**
	 * Validates the additional fields' values
	 *
	 * @param    array                    An array containing the data submitted by the add/edit task form
	 * @param    tx_scheduler_Module        Reference to the scheduler backend module
	 * @return    boolean                    True if validation was ok (or selected class is not relevant), false otherwise
	 */
	public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $schedulerModule) {
		$errors = array();

		$this->validateEIdUrl($submittedData, $errors);
		$this->validateMaxUrlsPerSitemap($submittedData, $errors);
		$this->validateIndexFilePath($submittedData, $errors);

		foreach ($errors as $error) {
			$error = $GLOBALS['LANG']->sL('LLL:EXT:dd_googlesitemap/locallang.xml:' . $error);
			t3lib_FlashMessageQueue::addMessage(t3lib_div::makeInstance('t3lib_FlashMessage', $error, '', t3lib_FlashMessage::ERROR));
		}

		return count($errors) == 0;
	}

	/**
	 * Takes care of saving the additional fields' values in the task's object
	 *
	 * @param array $submittedData An array containing the data submitted by the add/edit task form
	 * @param tx_ddgooglesitemap_indextask $task Reference to the task
	 * @return void
	 */
	public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task)
	{
		$task->setEIdScriptUrl($submittedData['eIdUrl']);
		$task->setMaxUrlsPerSitemap($submittedData['maxUrlsPerSitemap']);
		$task->setIndexFilePath($submittedData['indexFilePath']);
	}

	/**
	 * Validates the number of urls per sitemap.
	 *
	 * @param array $submittedData
	 * @param array $errors
	 * @return void
	 */
	protected function validateMaxUrlsPerSitemap(array &$submittedData, array &$errors) {
		$submittedData['maxUrlsPerSitemap'] = intval($submittedData['maxUrlsPerSitemap']);
		if ($submittedData['maxUrlsPerSitemap'] <= 0) {
			$errors[] = 'scheduler.error.badNumberOfUrls';
		}
	}

	/**
	 * Validates index file path.
	 *
	 * @param array $submittedData
	 * @param array $errors
	 * @return void
	 */
	protected function validateIndexFilePath(array &$submittedData, array &$errors) {
		if (t3lib_div::isAbsPath($submittedData['indexFilePath'])) {
			$errors[] = 'scheduler.error.badIndexFilePath';
		}
		else {
			$testPath = t3lib_div::getFileAbsFileName($submittedData['indexFilePath'], TRUE);
			if (!file_exists($testPath)) {
				if (!@touch($testPath)) {
					$errors[] = 'scheduler.error.badIndexFilePath';
				}
				else {
					unlink($testPath);
				}
			}
		}
	}

	/**
	 * Valies the URL of the eID script.
	 *
	 * @param array $submittedData
	 * @param array $errors
	 */
	protected function validateEIdUrl(array &$submittedData, array &$errors) {
		foreach (t3lib_div::trimExplode(chr(10), $submittedData['eIdUrl']) as $url) {
			if (FALSE !== ($urlParts = parse_url($url))) {
				if (!$urlParts['host']) {
					$errors[] = 'scheduler.error.missingHost';
				} else {
					list($count) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT(*) AS counter', 'sys_domain',
							'domainName=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($urlParts['host'], 'sys_domain')
					);
					if ($count['counter'] == 0) {
						$errors[] = 'scheduler.error.missingHost';
					}
				}
				if (!preg_match('/(?:^|&)eID=dd_googlesitemap/', $urlParts['query'])) {
					$errors[] = 'scheduler.error.badPath';
				}
				if (preg_match('/(?:^|&)(?:offset|limit)=/', $urlParts['query'])) {
					$errors[] = 'scheduler.error.badParameters';
				}
			}
		}
	}
}
