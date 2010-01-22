<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Dmitry Dulepov <dmitry@typo3.org>
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
 */


/**
 * Page and content manipulation watch.
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_ddgooglesitemap
 */
class tx_ddgooglesitemap_tcemain {

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

	/**
	 * Hooks to data change procedure to watch modified data. This hook is called
	 * after data is written to the database, so all paths are modified paths.
	 *
	 * @param	string	$status	Record status (new or update)
	 * @param	string	$table	Table name
	 * @param	integer	$id	Record ID
	 * @param	array	$fieldArray	Modified fields
	 * @param	object	$pObj	Reference to TCEmain
	 */
	public function processDatamap_afterDatabaseOperations($status, $table, $id, array $fieldArray, t3lib_TCEmain &$pObj) {
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
	 * @param	t3lib_TCEmain	$pObj	Reference to TCEmain
	 * @return	void
	 */
	protected function recordPageChange($table, $id, array $fieldArray, t3lib_TCEmain &$pObj) {
		if (($pid = $this->getPid($table, $id, $fieldArray, $pObj))) {
			$record = t3lib_BEfunc::getRecord('pages', $pid, 'tx_ddgooglesitemap_lastmod');
			$elements = $record['tx_ddgooglesitemap_lastmod'] == '' ? array() : t3lib_div::trimExplode(',', $record['tx_ddgooglesitemap_lastmod']);
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
				$tce = t3lib_div::makeInstance('t3lib_TCEmain');
				/* @var $tce t3lib_TCEmain */
				$tce->start($datamap, null);
				$tce->enableLogging = false;
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
	 * @param	t3lib_TCEmain	$pObj	Reference to TCEmain
	 * @return	void
	 */
	protected function getPid($table, $id, array $fieldArray, t3lib_TCEmain &$pObj) {
		if (!t3lib_div::testInt($id)) {
			$id = $pObj->substNEWwithIDs[$id];
		}
		if ($table !== 'pages') {
			if (isset($fieldArray['pid']) && t3lib_div::testInt($fieldArray['pid']) && $fieldArray['pid'] >= 0) {
				$id = $fieldArray['pid'];
			}
			else {
				$record = t3lib_BEfunc::getRecord($table, $id, 'pid');
				$id = $record['pid'];
			}
		}
		return $id;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap/class.tx_ddgooglesitemap_tcemain.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap/class.tx_ddgooglesitemap_tcemain.php']);
}

?>
