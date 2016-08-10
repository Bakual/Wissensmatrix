<?php
/**
 * @package     Wissensmatrix
 * @subpackage  mod_wissensmatrix_birthday
 *
 * @copyright   Copyright (C) 2016 Thomas Hunziker
 * @license     GNU General Public License version 2
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_wissensmatrix_birthday
 *
 * @package     Wissensmatrix
 * @subpackage  mod_wissensmatrix_birthday
 * @since       1.0
 */
abstract class ModWissensmatrixBirthdayHelper
{
	/**
	 * Retrieve a list of workers
	 *
	 * @param   \Joomla\Registry\Registry $params module parameters
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public static function getList($params)
	{
		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$catids = $user->getAuthorisedCategories('com_wissensmatrix', 'wissensmatrix.view.worker');
		$catids = implode(',', $catids);

		if (!$catids)
		{
			return array();
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$db->quoteName(
				array(
					'workers.id',
					'workers.name',
					'workers.vorname',
					'workers.geb',
				)
			)
		);
		$query->from($db->quoteName('#__wissensmatrix_mitarbeiter', 'workers'));
		$query->where($db->quoteName('workers.state') . ' = 1');
		$query->where($db->quoteName('workers.geb') . ' != ' . $db->quote($db->getNullDate()));
		$query->where($db->quoteName('workers.geb') . ' > CURDATE()');
		$query->where($db->quoteName('workers.geb') . ' <= DATE_ADD(NOW(), INTERVAL 90 DAY)');

		// Join over Workers Category.
		$query->select('c_workers.title AS category_title');
		$query->select('CASE WHEN CHAR_LENGTH(c_workers.alias) THEN CONCAT_WS(\':\', c_workers.id, c_workers.alias) ELSE c_workers.id END as catslug');
		$query->join('LEFT', '#__categories AS c_workers ON c_workers.id = workers.catid');
		$query->where('c_workers.published = 1');
		$query->where('c_workers.access IN (' . $groups . ')');
		$query->where('workers.catid IN (' . $catids . ')');

		$query->order($db->quoteName('workers.geb') . 'ASC');

		$db->setQuery($query);
		$items = $db->loadObjectList();

		return $items;
	}
}
