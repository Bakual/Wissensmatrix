<?php
/**
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Component Helper
jimport('joomla.application.component.helper');
jimport('joomla.application.categories');

/**
 * Wissensmatrix Component Route Helper
 *
 * @static
 * @package		Wissensmatrix
 * @since 4.0
 */
abstract class WissensmatrixHelperRoute
{
	protected static $lookup;

	public static function getWorkersRoute($catid = 0)
	{
		$needles = array(
			'workers' => array(0)
		);
		//Create the link
		$link = 'index.php?option=com_wissensmatrix&view=workers';
		if ($catid){
			$link .= '&teamid='.$catid;
		}

		if ($item = WissensmatrixHelperRoute::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		};

		return $link;
	}

	public static function getWorkerRoute($id)
	{
		$needles = array(
			'worker' => array((int)$id)
		);
		//Create the link
		$link = 'index.php?option=com_wissensmatrix&view=worker&id='.$id;

		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		} elseif ($item = self::_findItem()) {
			$link .= '&Itemid='.$item;
		} elseif ($item = self::_findItem(array('workers'=>array(0)))) {
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	public static function getReportsFwisRoute($id)
	{
		$needles = array(
			'id' => array((int)$id)
		);
		//Create the link
		$link = 'index.php?option=com_wissensmatrix&view=reportsfwis&id='.$id;

		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		} elseif ($item = self::_findItem()) {
			$link .= '&Itemid='.$item;
		} elseif ($item = self::_findItem(array('reportsfwigs'=>array(0)))) {
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	protected static function _findItem($needles = null)
	{
		$app	= JFactory::getApplication();
		$menus	= $app->getMenu('site');

		// Prepare the reverse lookup array.
		if (self::$lookup === null) {
			self::$lookup = array();

			$component	= JComponentHelper::getComponent('com_wissensmatrix');
			$items		= $menus->getItems('component_id', $component->id);
			if ($items){ // Populate static $lookup with Wissensmatrix menu entries: $lookup[view][id]
				foreach ($items as $item) {
					if (isset($item->query) && isset($item->query['view'])) {
						$view = $item->query['view'];
						if (!isset(self::$lookup[$view])) {
							self::$lookup[$view] = array();
						}
						if (isset($item->query['id'])) {
							self::$lookup[$view][$item->query['id']] = $item->id;
						} else {
							self::$lookup[$view][] = $item->id;
						}
					}
				}
			}
		}
		if ($needles) {
			foreach ($needles as $view => $ids) { // Search $lookup for matching menu entry
				if (isset(self::$lookup[$view])) {
					foreach($ids as $id) {
						if (isset(self::$lookup[$view][(int)$id])) {
							return self::$lookup[$view][(int)$id];
						}
					}
				}
			}
		} else { // Check if active menu entry is from Wissensmatrix
			$active = $menus->getActive();
			if ($active && $active->component == 'com_wissensmatrix') {
				return $active->id;
			}
		}

		return null;
	}
}
