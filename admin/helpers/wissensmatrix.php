<?php
defined('_JEXEC') or die;

class WissensmatrixHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 * @since	1.6
	 */
	public static function addSubmenu($vName = 'main')
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_WISSENSMATRIX_MENU_FWIS'),
			'index.php?option=com_wissensmatrix&view=fwis',
			$vName == 'fwis'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_WISSENSMATRIX_MENU_FWIGS'),
			'index.php?option=com_wissensmatrix&view=fwigs',
			$vName == 'fwigs'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_WISSENSMATRIX_MENU_WBIS'),
			'index.php?option=com_wissensmatrix&view=wbis',
			$vName == 'wbis'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_WISSENSMATRIX_MENU_WBIGS'),
			'index.php?option=com_wissensmatrix&view=wbigs',
			$vName == 'wbigs'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_WISSENSMATRIX_MENU_WORKERS'),
			'index.php?option=com_wissensmatrix&view=workers',
			$vName == 'workers'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_WISSENSMATRIX_MENU_CATEGORY_TEAMS'),
			'index.php?option=com_categories&extension=com_wissensmatrix',
			$vName == 'categories'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_WISSENSMATRIX_MENU_LEVELS'),
			'index.php?option=com_wissensmatrix&view=levels',
			$vName == 'levels'
		);
	}

	/**
	 * Get the actions for ACL
	 */
	public static function getActions($categoryId = 0)
	{
		$user  	= JFactory::getUser();
		$result	= new JObject;

		if (empty($categoryId))
		{
			$assetName = 'com_wissensmatrix';
		}
		else
		{
			$assetName = 'com_wissensmatrix.category.'.(int) $categoryId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}
}