<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class Com_WissensmatrixInstallerScript {

	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent) {
		// Create categories for our component
		$basePath = JPATH_ADMINISTRATOR.'/components/com_categories';
		require_once $basePath.'/models/category.php';
		$config		= array('table_path' => $basePath.'/tables');
		$catmodel	= new CategoriesModelCategory($config);
		$catData	= array('id' => 0, 'parent_id' => 0, 'level' => 1, 'path' => 'uncategorized', 'extension' => 'com_wissensmatrix',
						'title' => 'Uncategorized', 'alias' => 'uncategorized', 'description' => '', 'published' => 1, 'language' => '*');
		$catmodel->save($catData);

		$parent->getParent()->setRedirectURL('index.php?option=com_wissensmatrix');
	}

	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent) {
		echo '<div>'.JText::_('COM_WISSENSMATRIX_UNINSTALL_TEXT').'</div>';
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent) {
	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent) {
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
 	function postflight($type, $parent) {
		echo JText::sprintf('COM_WISSENSMATRIX_POSTFLIGHT', $type);
	}
}