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

use DmitryDulepov\DdGooglesitemap\Renderers\AbstractSitemapRenderer;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class is a base for all sitemap generators.
 *
 * @author Dmitry Dulepov <support@snowflake.ch>
 */
abstract class AbstractSitemapGenerator {
	/**
	 * cObject to generate links
	 *
	 * @var	\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	protected $cObj;

	/**
	 * Maximum number of items to show.
	 *
	 * @var int
	 */
	protected $limit;

	/**
	 * Offset to start outputting from.
	 *
	 * @var int
	 */
	protected $offset;

	/**
	 * A sitemap renderer
	 *
	 * @var	AbstractSitemapRenderer
	 */
	protected $renderer;

	/**
	 * Class name to instantiate the renderer.
	 *
	 * @var string
	 */
	protected $rendererClass = 'DmitryDulepov\\DdGooglesitemap\\Renderers\\StandardSitemapRenderer';

	/**
	 * Initializes the instance of this class. This constructir sets starting
	 * point for the sitemap to the current page id
	 */
	public function __construct() {
		$this->cObj = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
		$this->cObj->start(array());

		$this->offset = max(0, (int)GeneralUtility::_GET('offset'));
		$this->limit = max(0, (int)GeneralUtility::_GET('limit'));
		if ($this->limit <= 0) {
			$this->limit = 50000;
		}

		$this->createRenderer();
	}

	/**
	 * Writes sitemap content to the output.
	 *
	 * @return void
	 */
	public function main() {
		header('Content-type: text/xml');
		if ($this->renderer) {
			echo $this->renderer->getStartTags();
		}
		$this->generateSitemapContent();
		if ($this->renderer) {
			echo $this->renderer->getEndTags();
		}
	}

	/**
	 * Creates the renderer using $this->rendererClass. Subclasses can use a
	 * more flexible logic if just setting the class is not enough.
	 *
	 * @return void
	 */
	protected function createRenderer() {
		$this->renderer = GeneralUtility::makeInstance($this->rendererClass);
	}

	/**
	 * Generates the sitemap and echoes it to the browser.
	 *
	 * @return void
	 */
	abstract protected function generateSitemapContent();
}
