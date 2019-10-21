<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2011 Wolfgang Rotschek <scotty@dev-null.at>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is 
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


use \TYPO3\CMS\Frontend\Page\PageRepository as t3lib_pageSelect;
use \TYPO3\CMS\Backend\Tree\View\PageTreeView as t3lib_pageTree;
use \TYPO3\CMS\Core\Utility\GeneralUtility as t3lib_div;
use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility as t3lib_extMgm;
use \TYPO3\CMS\Frontend\Plugin\AbstractPlugin as tslib_pibase;
use \TYPO3\CMS\Core\TypoScript\TemplateService as t3lib_tstemplate;

// base class
require_once(t3lib_extMgm::extPath('dev_null_seo', 'renderer/class.tx_devnullseo_render_abstract.php'));

class tx_devnullseo_render_pages extends tx_devnullseo_render_abstract
{
	var $confPages = array();
	
	// the array of pages for the sitemap
	var $conf_depth = 10;
	
	var $conf_addRootShortcut = 0;
	
	var $conf_addShortcut = 0;
	
	var $conf_addNavHide  = 0;
	
	var $conf_addPageHide = 0;
	
	var $conf_addNoSearch = 0;
	
	public function getXmlWrapName() {
		return 'urlset';
	}
	
	public function getIncludeRenderer($name, $pageConfig) {
		
		$includeKey = $pageConfig['sitemap.']['pages.'][$name . '.']['renderer'];
		if($includeKey) {
			$renderer = $pageConfig['rendererPages.'][$includeKey];
		} else {
			$renderer = $pageConfig['rendererPages.'][$name];
		}

		// t3lib_utility_Debug::debug($renderer);

		if(!$renderer)
			return NULL;

		// get single renderer config fields (Extension : file name : class)
		$_cfg = explode(':', $renderer);

		// load file
		require_once(t3lib_extMgm::extPath($_cfg[0], $_cfg[1]));
		
		// return instance
		return t3lib_div::makeInstance($_cfg[2]);
	}
	
	/**
	 * Generates a XML sitemap from the page structure
	 *
	 * @page        int		uid of starting page
	 * @return      string	the XML sitemap ready to render
	 */
	public function renderItems($page, $conf, $section = 'pages') {
	
		$this->confPages = $this->conf['sitemap.']['pages.'];
		
		// uncomment for debugging
		// t3lib_utility_Debug::printArray($this->confPages, 'confPages');

		// get configuration

		$pidList = array();
		
		// do we have a page list
		if(empty($this->confPages['pidList']))
			$pidList[] = $page;
		else
			$pidList += explode(',', $this->confPages['pidList']);

		// include root page even if shortcut if set
		if(array_key_exists('inludeRootShortcut', $this->confPages)) {
			$this->conf_addRootShortcut = intval($this->confPages['inludeRootShortcut']);
		}
		
		// add shortcut if set
		if(array_key_exists('addShortcut', $this->confPages)) {
			$this->conf_addShortcut = intval($this->confPages['addShortcut']);
		}
		
		// add pages with hide for navigation if set
		if(array_key_exists('addNavHide', $this->confPages)) {
			$this->conf_addNavHide = intval($this->confPages['addNavHide']);
		}
		
		// add pages with no search
		if(array_key_exists('addNoSearch', $this->confPages)) {
			$this->conf_addNoSearch = intval($this->confPages['addNoSearch']);
		}
		
		// how many levels into the page tree
		if(array_key_exists('depth', $this->confPages)) {
			$this->conf_depth = intval($this->confPages['depth']);
		}

		// get root page
		$uidRoot = $rootline[0]['uid'];
		
		$pageSelect = t3lib_div::makeInstance(t3lib_pageSelect::class);
		$pageSelect->init(true);

		// process the pidList
		foreach($pidList as $pid) {
			$this->addPageTree($pid);
		}
	}


	/**
	 * Adds a given page and the subpages to the pagelist of the sitemap
	 *
	 * @param	array	$pageId	Page ID
	 * @return	array	list of page records
	 */
	protected function addPageTree($pageID)
	{
		$pageSelect = t3lib_div::makeInstance(t3lib_pageSelect::class);
		$pageSelect->init(true);
		
		// t3lib_utility_Debug::debug($pageID, 'addPageTree(pageID)');
		// t3lib_utility_Debug::debug($this->conf_depth, 'this->conf_depth');

		$pRoot = $pageSelect->getPage($pageID);

		if(count($pRoot) != 0) {
			if($pRoot['doktype'] != 4 || $this->conf_addRootShortcut) {
				$this->addSinglePage($pRoot);
			}
		}

		// no sub pages?
		if($this->conf_depth == 0)
			return;
			
		$treeClause = array(
			'deleted = 0',								// no deleted pages
			'hidden = 0',								// no hidden pages
			'(starttime = 0 || starttime > NOW())',		// starttime
			'(endtime = 0 || endtime < NOW())',			// endtime
			'doktype NOT IN (199, 254, 255)',			// document types
			'fe_group = ""',							// only unrestricted pages
		);

		if($this->conf_addNavHide == 0) {
		 	$treeClause[] = 'nav_hide = 0';				// hide in menu
		}

		if($this->conf_addNoSearch == 0) {
		 	$treeClause[] = 'no_search = 0';			// no search
		}

		// create pageTree object
		$tree = t3lib_div::makeInstance(t3lib_pageTree::class);
		
		$tree->addField('SYS_LASTCHANGED', 1);
		$tree->addField('crdate', 1);
		$tree->addField('no_search', 1);

		$tree->init(' AND '. implode(' AND ', $treeClause));

		// create the tree from starting point
		$tree->getTree($pageID, $this->conf_depth, '');

		// uncomment for debugging
		// t3lib_utility_Debug::debug($tree->tree, 'page tree');
		// t3lib_utility_Debug::debug(count($tree->tree), 'tree count');

		foreach($tree->tree as $treeRow) {

			if($treeRow['row']['doktype'] != 4 || $this->conf_addShortcut) {
				$this->addSinglePage($treeRow['row']);
			}
		}
	}

	
	protected function addSinglePage($row)
	{
		// uncomment for debugging
		// t3lib_utility_Debug::debug($row['uid'], 'uid');

		$_conf = $this->loadTypoScriptForPage($row['uid']);
		
		$lastmod = ($row['SYS_LASTCHANGED'] ? $row['SYS_LASTCHANGED'] : $row['crdate']);

		// format date, see http://www.w3.org/TR/NOTE-datetime for possible formats
		// if version is php5 or higher, we use "c" for the complete datetime
		$timeident = (str_replace('.', '', phpversion()) >= 500 ? "c" : "Y-m-d");
		$lastmod = date($timeident, $lastmod);

		$url1 = $this->cObj->getTypoLink_URL($row['uid']);
		$url = $this->getPageLink($row['uid']);
		
		$nodeItems = array();
		$nodeItems[] = $this->wrapXmlItem('loc', $url);
		$nodeItems[] = $this->wrapXmlItem('lastmod', $lastmod);
		// $nodeItems[] = $this->wrapXmlItem('comment', $row['uid']);
		// $nodeItems[] = $this->wrapXmlItem('comment', $url1);

		// t3lib_utility_Debug::printArray($_conf, '_conf');
		// t3lib_utility_Debug::debug($_conf['sitemap.']['pages.']['include'], 'pages.include');
		
		foreach(explode(',', $_conf['sitemap.']['pages.']['include']) as $include) {
			$_renderer = $this->getIncludeRenderer($include, $_conf);
			
			if($_renderer) {
			 	$_renderer->init($this->cObj, $_conf);
				$_renderer->renderItems($row['uid'], $_conf, $include);
				
				$nodeItems[] = $this->wrapXmlItem($_renderer->getXmlWrapName(), $_renderer->items);
			}
		}

		$xml = $this->wrapXmlItem('url', $nodeItems);
		
		// uncomment for debugging
		// t3lib_utility_Debug::debug($xml, 'xml');
		
		$this->items[$row['uid']] = $xml;

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
	
	/**
	* Loads the TypoScript for the given page.
	*
	* @param int $pageUID
	* @return array
	*/
	function loadTypoScriptForPage($pageUID) {

		$_rootLine = $GLOBALS['TSFE']->sys_page->getRootLine($pageUID);
		
		$_TSObj = t3lib_div::makeInstance(\TYPO3\CMS\Core\TypoScript\ExtendedTemplateService::class);
		$_TSObj->tt_track = 0;
		$_TSObj->init();
		$_TSObj->runThroughTemplates($_rootLine);
		$_TSObj->generateConfig();

		return $_TSObj->setup['config.']['devnullseo.'];
	}
}

?>
