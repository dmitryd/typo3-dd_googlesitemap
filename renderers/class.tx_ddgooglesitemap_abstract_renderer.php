<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Dmitry Dulepov <dmitry@typo3.org>
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
 * This class contains an abstract renderer for sitemaps.
 *
 * NOTE: interface is internal and it is not stable. Any XCLASS is not guarantied
 * to work!
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_ddgooglesitemap
 */
abstract class tx_ddgooglesitemap_abstract_renderer {

	/**
	 * Creates start XML tags (including XML prologue) for the sitemap.
	 *
	 * @return	string	Start tags
	 */
	abstract public function getStartTags();

	/**
	 * Renders one single entry according to the format of this sitemap.
	 *
	 * @param	string	$url	URL of the entry
	 * @param	string	$title	Title of the entry
	 * @param	int	$lastModification	News publication time (Unix timestamp)
	 * @param	string	$changeFrequency	Unused for news
	 * @param	string	$keywords	Keywords for this entry
	 * @return	string	Generated entry content
	 * @see tx_ddgooglesitemap_abstract_renderer::renderEntry()
	 */
	abstract public function renderEntry($url, $title, $lastModification = 0, $changeFrequency = '', $keywords = '');

	/**
	 * Creates end XML tags for this sitemap.
	 *
	 * @return	string	End XML tags
	 */
	abstract public function getEndTags();

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap/renderers/class.tx_ddgooglesitemap_abstract_renderer.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap/renderers/class.tx_ddgooglesitemap_abstract_renderer.php']);
}

?>