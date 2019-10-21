<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2007-2008 Benjamin Mack (www.xnos.org) 
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

use \TYPO3\CMS\Core\Utility\GeneralUtility as t3lib_div;
use \TYPO3\CMS\Frontend\Plugin\AbstractPlugin as tslib_pibase;
use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility as t3lib_extMgm;

class tx_devnullseo_sitemap extends tslib_pibase{

	var $mapItems = array();
	
	/**
	 * Generates a XML sitemap from the page structure
	 *
	 * @param       string	the content to be filled, usually empty
	 * @param       array	additional configuration parameters
	 * @return      string	the XML sitemap ready to render
	 */
	function main($content, $conf) {

		// get original page uid from GET/POST vars
		$pageUID = t3lib_div::_GP('id');

		// ignore pssed $conf and get the whole config for usetx_devnullseo_render_pages
		// $conf contains only the sitmap subnode but wee need all
		$this->conf = $GLOBALS['TSFE']->tmpl->setup['config.']['devnullseo.'];
		// get piVars - should be empty
		$this->pi_setPiVarDefaults();
		// get localisation
		$this->pi_loadLL();

		// uncomment for debugging
		// t3lib_utility_Debug::printArray($this->conf);
		
		// which main renderers to use
		$includes = $this->conf['sitemap.']['include'];
		
		// for each main include
		foreach(explode(',', $includes) as $rendererKey) {

			// get extension key, file and class
			$_cfg = explode(':', $this->conf['rendererSitemap.'][$rendererKey]);
			
			// load file
			$includeFile = t3lib_extMgm::extPath($_cfg[0], $_cfg[1]);
			require_once($includeFile);
			
			// create instance
			$renderer = t3lib_div::makeInstance($_cfg[2]);

			// store standard cObj
			$renderer->init($this->cObj, $this->conf);
						
			$renderer->renderItems($pageUID, $this->conf);

			$xml = implode("\n", $renderer->items);
			
			$this->mapItems[] = $renderer->wrapXmlItem($renderer->getXmlWrapName(), $xml);

			// uncomment for debugging
			// t3lib_utility_Debug::debug($xml);
		}
		
		return implode("\n", $this->mapItems);
		
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dev_null_seo/class.tx_devnullseo_sitemap.php']) {
   include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dev_null_seo/class.tx_devnullseo_sitemap.php']);
}
?>
