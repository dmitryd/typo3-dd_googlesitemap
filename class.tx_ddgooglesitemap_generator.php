<?php

abstract class tx_ddgooglesitemap_generator {
	/**
	 * cObject to generate links
	 *
	 * @var	tslib_cObj
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
	 * @var	tx_ddgooglesitemap_abstract_renderer
	 */
	protected $renderer;

	/**
	 * Class name to instantiate the renderer.
	 *
	 * @var string
	 */
	protected $rendererClass = 'tx_ddgooglesitemap_normal_renderer';

	/**
	 * Initializes the instance of this class. This constructir sets starting
	 * point for the sitemap to the current page id
	 *
	 * @return	void
	 */
	public function __construct() {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->cObj->start(array());

		$this->offset = max(0, intval(t3lib_div::_GET('offset')));
		$this->limit = max(0, intval(t3lib_div::_GET('limit')));
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
		$this->renderer = t3lib_div::makeInstance($this->rendererClass);
	}

	/**
	 * Generates the sitemap and echoes it to the browser.
	 *
	 * @return void
	 */
	abstract protected function generateSitemapContent();
}
