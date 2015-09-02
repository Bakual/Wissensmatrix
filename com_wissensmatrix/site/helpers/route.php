<?php
/**
 * @copyright      Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
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
 * @package        Wissensmatrix
 * @since          4.0
 */
abstract class WissensmatrixHelperRoute
{
	protected static $lookup = array();
	protected static $langs;

	public static function getWorkersRoute($teamid = 0, $language = 0)
	{
		$needles = array(
			'workers' => array(0),
		);
		//Create the link
		$link = 'index.php?option=com_wissensmatrix&view=workers';
		if ($teamid)
		{
			$link .= '&teamid=' . $teamid;
		}

		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			if (!isset(self::$langs))
			{
				self::_getLanguages();
			}
			foreach (self::$langs as $lang)
			{
				if ($language == $lang->lang_code)
				{
					$link .= '&lang=' . $lang->sef;
					$needles['language'] = $language;
				}
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}
		elseif ($item = self::_findItem())
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	public static function getWorkerRoute($id, $language = 0)
	{
		$needles = array(
			'worker'  => array((int) $id),
			'workers' => array(0),
		);
		//Create the link
		$link = 'index.php?option=com_wissensmatrix&view=worker&id=' . $id;

		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			if (!isset(self::$langs))
			{
				self::_getLanguages();
			}
			foreach (self::$langs as $lang)
			{
				if ($language == $lang->lang_code)
				{
					$link .= '&lang=' . $lang->sef;
					$needles['language'] = $language;
				}
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}
		elseif ($item = self::_findItem())
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	public static function getReportsFwisRoute($id, $language = 0)
	{
		$needles = array(
			'reportsfwis'  => array((int) $id),
			'reportsfwis'  => array(0),
			'reportsfwigs' => array(0),
		);
		//Create the link
		$link = 'index.php?option=com_wissensmatrix&view=reportsfwis&id=' . $id;

		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			if (!isset(self::$langs))
			{
				self::_getLanguages();
			}
			foreach (self::$langs as $lang)
			{
				if ($language == $lang->lang_code)
				{
					$link .= '&lang=' . $lang->sef;
					$needles['language'] = $language;
				}
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}
		elseif ($item = self::_findItem())
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	public static function getReportsWbisRoute($id, $language = 0)
	{
		$needles = array(
			'reportswbis'  => array((int) $id),
			'reportswbis'  => array(0),
			'reportswbigs' => array(0),
		);
		//Create the link
		$link = 'index.php?option=com_wissensmatrix&view=reportswbis&id=' . $id;

		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			if (!isset(self::$langs))
			{
				self::_getLanguages();
			}
			foreach (self::$langs as $lang)
			{
				if ($language == $lang->lang_code)
				{
					$link .= '&lang=' . $lang->sef;
					$needles['language'] = $language;
				}
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}
		elseif ($item = self::_findItem())
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	protected static function _findItem($needles = null)
	{
		$app      = JFactory::getApplication();
		$menus    = $app->getMenu('site');
		$language = isset($needles['language']) ? $needles['language'] : '*';

		// Prepare the reverse lookup array.
		if (!isset(self::$lookup[$language]))
		{
			self::$lookup[$language] = array();

			$component = JComponentHelper::getComponent('com_wissensmatrix');

			$attributes = array('component_id');
			$values     = array($component->id);

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[]     = array($needles['language'], '*');
			}

			$items = $menus->getItems($attributes, $values);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];
					if (!isset(self::$lookup[$language][$view]))
					{
						self::$lookup[$language][$view] = array();
					}
					if (isset($item->query['id']))
					{
						if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*')
						{
							self::$lookup[$language][$view][$item->query['id']] = $item->id;
						}
					}
					else
					{
						if (!isset(self::$lookup[$language][$view][0]) || $item->language != '*')
						{
							self::$lookup[$language][$view][0] = $item->id;
						}
					}
				}
			}
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$language][$view]))
				{
					foreach ($ids as $id)
					{
						if (isset(self::$lookup[$language][$view][(int) $id]))
						{
							return self::$lookup[$language][$view][(int) $id];
						}
					}
				}
			}
		}

		// Check for an active Wissensmatrix menuitem
		$active = $menus->getActive();
		if ($active && $active->component == 'com_wissensmatrix' && ($active->language == '*' || !JLanguageMultilang::isEnabled()))
		{
			return $active->id;
		}

		if (!$needles)
		{
			// Get first Wissensmatrix menuitem found
			if (isset(self::$lookup[$language]))
			{
				$first = self::$lookup[$language];

				return reset($first);
			}

			// if not found in second try, return language specific home link
			$default = $menus->getDefault($language);

			return !empty($default->id) ? $default->id : null;
		}

		return;
	}

	protected static function _getLanguages()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.sef AS sef')
			->select('a.lang_code AS lang_code')
			->from('#__languages AS a');

		$db->setQuery($query);
		self::$langs = $db->loadObjectList();

		return;
	}
}
