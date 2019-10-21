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


use \TYPO3\CMS\Core\Utility\GeneralUtility as t3lib_div;
use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility as t3lib_extMgm;
use \TYPO3\CMS\Core\Database\ConnectionPool;

// base class
require_once(t3lib_extMgm::extPath('dev_null_seo', 'renderer/class.tx_devnullseo_render_abstract.php'));

class tx_devnullseo_render_images extends tx_devnullseo_render_abstract
{

	public function getXmlWrapName() {
		return 'pageItems';
	}
	
	public function getIncludeRenderer($name, $pageConfig) {
		return NULL;
	}
	
	public function renderItems($page, $config, $section = NULL) {

        if(t3lib_extMgm::isLoaded('dam_ttcontent')) {
			$this->items[] = '<!-- Error: tx_devnullseo_render_dam_ttcontent - dam_ttcontent conflicts -->';
			return;
		}

        $queryBuilder = t3lib_div::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $result = $queryBuilder
            ->select('*')
            ->from('sys_file_reference', 'fileRef')
            ->leftJoin(
                'fileRef',
                'sys_file',
                'file',
                $queryBuilder->expr()->eq('file.uid', $queryBuilder->quoteIdentifier('fileRef.uid_local'))
            )
            ->join(
                'fileRef',
                'tt_content',
                'ttc',
                $queryBuilder->expr()->eq('ttc.uid', $queryBuilder->quoteIdentifier('fileRef.uid_foreign'))
            )
            ->where($queryBuilder -> expr ( ) -> eq ('ttc.deleted',0))
            ->andWhere($queryBuilder -> expr ( ) -> neq ('ttc.image',0))
            ->andWhere($queryBuilder -> expr ( ) -> eq ('ttc.pid', $page))
            ->execute();
        $rows = $result->fetchAll();

        foreach ($rows As $dk =>$dv) {
            if(!$dv['title'])
				continue;

			$_captions 	= explode("\r\n", $dv['description']);
			$_copyright = $this->getPageLink($page);

            $url = $GLOBALS['TSFE']->baseUrl.'fileadmin'.$dv['identifier'];
            $nodeItems = array();
            $nodeItems[] = $this->wrapXmlItem('imageLoc', $url);
            $nodeItems[] = $this->wrapXmlItem('imageCaption', $dv['description'] ? $dv['description'] : $dv['title']);
            $nodeItems[] = $this->wrapXmlItem('imageLicense', $_copyright);
            $xml = $this->wrapXmlItem('title', implode("\n", $nodeItems));
            $this->items[$dk['name']] = $xml;

        }
		// $GLOBALS['TYPO3_DB']->sql_free_result($dbRes);

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
	 * Creates a link to a single image
	 *
	 * @param	array	$pageId	Page ID
	 * @return	string	Full URL of the page including host name (escaped)
	 */
	protected function getImageLink($image) {

		$link = htmlspecialchars('uploads/pics/' . $image);
		return t3lib_div::locationHeaderUrl($link);
	}

}
?>
