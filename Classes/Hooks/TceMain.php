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

namespace DmitryDulepov\DdGooglesitemap\Hooks;

use \TYPO3\CMS\Backend\Utility\BackendUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Page and content manipulation watch.
 *
 * @author	Dmitry Dulepov <dmitry.dulepov@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_ddgooglesitemap
 */
class TceMain {

	/**
	 * If > 1 than we are in the recursive call to ourselves and we do not do anything
	 *
	 * @var	int
	 */
	protected $lock = 0;

	/**
	 * Maximum number of timestamps to save
	 *
	 */
	const MAX_ENTRIES = 5;

	/** @var int[] */
	static protected $recordedPages = array();

	/**
	 * Hooks to data change procedure to watch modified data. This hook is called
	 * after data is written to the database, so all paths are modified paths.
	 *
	 * @param string $status Record status (new or update)
	 * @param string $table Table name
	 * @param int $id Record ID
	 * @param array $fieldArray Modified fields
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $pObj Reference to TCEmain
	 */
	public function processDatamap_afterDatabaseOperations(/** @noinspection PhpUnusedParameterInspection */ $status, $table, $id, array $fieldArray, \TYPO3\CMS\Core\DataHandling\DataHandler &$pObj) {
		// Only for LIVE records!
		if ($pObj->BE_USER->workspace == 0 && !$this->lock) {
			$this->lock++;
			$this->recordPageChange($table, $id, $fieldArray, $pObj);
			$this->lock--;
		}
	}

	/**
	 * Records page change time in our own field
	 *
	 * @param	string	$table	Table name
	 * @param	int	$id	ID of the record
	 * @param	array	$fieldArray	Field array
	 * @param	\TYPO3\CMS\Core\DataHandling\DataHandler	$pObj	Reference to TCEmain
	 * @return	void
	 */
	protected function recordPageChange($table, $id, array $fieldArray, \TYPO3\CMS\Core\DataHandling\DataHandler &$pObj) {
		if (($pid = $this->getPid($table, $id, $fieldArray, $pObj)) && !isset(self::$recordedPages[$pid])) {
			self::$recordedPages[$pid] = 1;

			$record = BackendUtility::getRecord('pages', $pid, 'tx_ddgooglesitemap_lastmod');
			$elements = $record['tx_ddgooglesitemap_lastmod'] == '' ? array() : GeneralUtility::trimExplode(',', $record['tx_ddgooglesitemap_lastmod']);
			$time = time();
			// We must check if this time stamp is already in the list. This
			// happens with many independent updates of the page during a
			// single TCEmain action
			if (!in_array($time, $elements)) {
				$elements[] = $time;
				if (count($elements) > self::MAX_ENTRIES) {
					$elements = array_slice($elements, -self::MAX_ENTRIES);
				}

				$datamap = array(
					'pages' => array(
						$pid => array(
							'tx_ddgooglesitemap_lastmod' => implode(',', $elements),
						),
					),
				);
				$tce = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
				/* @var $tce \TYPO3\CMS\Core\DataHandling\DataHandler */
				$tce->start($datamap, NULL);
				$tce->enableLogging = FALSE;
				$tce->process_datamap();
			}
		}
	}

	/**
	 * Obtains page id from the arguments
	 *
	 * @param	string	$table	Table name
	 * @param	int	$id	ID of the record
	 * @param	array	$fieldArray	Field array
	 * @param	\TYPO3\CMS\Core\DataHandling\DataHandler	$pObj	Reference to TCEmain
	 * @return	int
	 */
	protected function getPid($table, $id, array $fieldArray, \TYPO3\CMS\Core\DataHandling\DataHandler &$pObj) {
		if (!MathUtility::canBeInterpretedAsInteger($id)) {
			$id = $pObj->substNEWwithIDs[$id];
		}
		if ($table !== 'pages') {
			if (isset($fieldArray['pid']) && MathUtility::canBeInterpretedAsInteger($fieldArray['pid']) && $fieldArray['pid'] >= 0) {
				$id = $fieldArray['pid'];
			}
			else {
				$record = BackendUtility::getRecord($table, $id, 'pid');
				$id = $record['pid'];
			}
		}
		return $id;
	}
}
