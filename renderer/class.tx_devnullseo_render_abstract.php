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

// Typo3 debugging utility
// require_once(PATH_t3lib.'utility/class.t3lib_utility_debug.php');

abstract class tx_devnullseo_render_abstract{

	var $conf;

	var $cObj;
	
	var $items = array();
	
	public function init($cObj, $conf) {
		$this->cObj = $cObj;
		$this->conf = $conf;		
	}
	
	public function wrapXmlItem($name, $content) {
		
		$wrap = $this->conf['xmlWraps.'][$name . '.'];
		
		if(is_array($content))
			$content = implode("\n", $content);
			
		// t3lib_utility_Debug::debug($wrap, "wrap $name");

		return $this->cObj->stdWrap($content, $wrap);
	}
	
	public abstract function getXmlWrapName();

	public abstract function getIncludeRenderer($name, $pageConfig);
	
	public abstract function renderItems($page, $config, $section = NULL);
}
?>
