<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Wolfgang <scotty@dev-null.at>
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
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'dev/null xml-Sitemap' for the 'dev_null_seo' extension.
 *
 * @author	Wolfgang <scotty@dev-null.at>
 * @package	TYPO3
 * @subpackage	tx_devnullseo
 */
class tx_devnullseo_pi1 extends tslib_pibase {
	
	// Same as class name
	var $prefixId      = 'tx_devnullseo_pi1';
	
	// Path to this script relative to the extension dir.
	var $scriptRelPath = 'pi1/class.tx_devnullseo_pi1.php';
	
	// The extension key.
	var $extKey        = 'dev_null_seo';
	
	var $pi_checkCHash = true;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf)	{
		return 'Hello World!<HR>
			Here is the TypoScript passed to the method:'.
					t3lib_div::view_array($conf);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dev_null_seo/pi1/class.tx_devnullseo_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dev_null_seo/pi1/class.tx_devnullseo_pi1.php']);
}

?>