<?php
/**
 * @copyright      Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * @package        Wissensmatrix
 */
// Based on com_contact
class WissensmatrixModelWbis extends JModelList
{
	protected $_item = null;
	protected $_siblings = null;
	protected $_children = null;
	protected $_parent = null;

	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'wbis.title_de', 'wbis.title_fr', 'wbis.title_it',
				'title', 'title_de', 'title_fr', 'title_it',
				'ordering', 'wbis.ordering',
				'checked_out', 'wbis.checked_out',
				'checked_out_time', 'wbis.checked_out_time',
				'language', 'wbis.language',
				'hits', 'wbis.hits',
				'category_title', 'c_wbis.category_title',
				'zwbi.status_id', 'zwbi.date',
			);
		}

		parent::__construct($config);
	}

	protected function getListQuery()
	{
		$db = $this->getDbo();
		if ($this->getState('userreport'))
		{
			// Fake accesslevel for userreports since we don't have a logged-in user here.
			$query = $db->getQuery(true)
				->select('id')
				->from('#__viewlevels');

			$db->setQuery($query);
			$levels = $db->loadColumn();

			$groups = implode(',', $levels);
		}
		else
		{
			$user   = JFactory::getUser();
			$groups = implode(',', $user->getAuthorisedViewLevels());
		}

		// Create a new query object.
		$query = $db->getQuery(true);

		// Select required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'wbis.id, wbis.catid, wbis.refresh, ' .
				'CASE WHEN CHAR_LENGTH(wbis.alias) THEN CONCAT_WS(\':\', wbis.id, wbis.alias) ELSE wbis.id END as slug, ' .
				'wbis.hits, wbis.alias, wbis.checked_out, wbis.checked_out_time,' .
				'wbis.state, wbis.ordering, wbis.created, wbis.created_by'
			)
		);
		$query->from('`#__wissensmatrix_weiterbildung` AS wbis');

		// Create title from active language
		$lang = substr(JFactory::getLanguage()->getTag(), 0, 2);
		$query->select('wbis.`title_' . $lang . '` AS title');

		// Join over Wbis Category.
		$query->select('c_wbis.title AS category_title');
		$query->select('CASE WHEN CHAR_LENGTH(c_wbis.alias) THEN CONCAT_WS(\':\', c_wbis.id, c_wbis.alias) ELSE c_wbis.id END as catslug');
		$query->join('LEFT', '#__categories AS c_wbis ON c_wbis.id = wbis.catid');
		$query->where('c_wbis.access IN (' . $groups . ')');
		$query->where('c_wbis.published = 1');

		// Filter by category
		if ($categoryId = $this->getState('category.id'))
		{
			if ($levels = (int) $this->getState('filter.subcategories', 0))
			{
				// Create a subquery for the subcategory list
				$subQuery = $db->getQuery(true);
				$subQuery->select('sub.id');
				$subQuery->from('#__categories as sub');
				$subQuery->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt');
				$subQuery->where('this.id = ' . (int) $categoryId);
				if ($levels > 0)
				{
					$subQuery->where('sub.level <= this.level + ' . $levels);
				}
				// Add the subquery to the main query
				$query->where('(wbis.catid = ' . (int) $categoryId
					. ' OR wbis.catid IN (' . $subQuery->__toString() . '))');
			}
			else
			{
				$query->where('wbis.catid = ' . (int) $categoryId);
			}
		}

		// Join over users for the author names.
		$query->select("user.name AS author");
		$query->join('LEFT', '#__users AS user ON user.id = wbis.created_by');

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');
			$query->where('(wbis.title_' . $lang . ' LIKE ' . $search . ')');
		}

		// Filter by state
		$state = $this->getState('filter.state');
		if (is_numeric($state))
		{
			$query->where('wbis.state = ' . (int) $state);
		}
		elseif (is_array($state))
		{
			$state = \Joomla\Utilities\ArrayHelper::toInteger($state);
			$query->where('wbis.state IN (' . implode(',', $state) . ')');
		}

		// Join over Wbigs.
		$query->select('wbigs.title_' . $lang . ' AS wbig_title, wbigs.id AS wbig_id');
		$query->select('CASE WHEN CHAR_LENGTH(wbigs.alias) THEN CONCAT_WS(\':\', wbigs.id, wbigs.alias) ELSE wbigs.id END as wbigslug');
		$query->join('LEFT', '#__wissensmatrix_weiterbildunggruppe AS wbigs ON wbigs.id = wbis.wbig_id');

		// Filter by wbig (needed in reportswbis view)
		if ($wbigId = $this->getState('wbig.id'))
		{
			$query->where('wbigs.id = ' . (int) $wbigId);
		}

		// Filter by worker (needed in worker and reportwbigteam view)
		if ($workerId = $this->getState('worker.id'))
		{
			if (is_array($workerId))
			{
				$workerId = \Joomla\Utilities\ArrayHelper::toInteger($workerId);
				$query->where('mit_id IN (' . implode(',', $workerId) . ')');
				$query->select('count(1) as mit_count');
				$query->group('zwbi.wbi_id');
			}
			else
			{
				$query->select('CASE WHEN wbis.refresh THEN DATEDIFF(DATE_ADD(zwbi.date, INTERVAL wbis.refresh YEAR), NOW()) ELSE 0 END as zwbi_refresh');
				$query->select('zwbi.id as zwbi_id, zwbi.status_id as zwbi_status_id, zwbi.bemerkung, zwbi.date, mit_id');
				$query->where('mit_id = ' . (int) $workerId);
			}
			$query->join('LEFT', '#__wissensmatrix_mit_wbi AS zwbi ON zwbi.wbi_id = wbis.id');
		}

		// Filter by wbis.refresh
		if ($this->getState('filter.wbirefresh'))
		{
			$query->where('wbis.refresh > 0');
		}

		// Filter by zwbi.status (needed in worker (?) and reportwbigteam view)
		if ($zwbistate = $this->getState('filter.zwbistate'))
		{
			$query->where('zwbi.status_id = ' . (int) $zwbistate);
		}

		// Filter by language
		if ($this->getState('filter.language'))
		{
			$query->where('wbis.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'ordering')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since    1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app    = JFactory::getApplication();
		$params = $app->getParams();
		$this->setState('params', $params);
		$jinput = $app->input;

		// Category filter (priority on request so subcategories work)
		// Team in this case, not used but selection has to be saved in userstate
		$teamid = $this->getUserStateFromRequest('com_wissensmatrix.team.id', 'teamid', null, 'int');

		// Check default team
		if ($teamid === null)
		{
			$teamid = WissensmatrixHelperWissensmatrix::getDefaultTeam();
			JFactory::getApplication()->setUserState('com_wissensmatrix.team.id', $teamid);
		}

		$this->setState('team.id', $teamid);

		// Category filter (priority on request so subcategories work)
		// We don't use userstate here as the category is not used as team but category.
		$id = $jinput->get('catid', $params->get('catid', 0), 'int');
		$this->setState('category.id', $id);

		// Include Subcategories or not
		$this->setState('filter.subcategories', $params->get('show_subcategory_content', 0));

		$user = JFactory::getUser();
		if ((!$user->authorise('core.edit.state', 'com_wissensmatrix')) && (!$user->authorise('core.edit', 'com_wissensmatrix')))
		{
			// filter on published/archived for those who do not have edit or edit.state rights.
			$this->setState('filter.state', array(1,2));
		}

		$this->setState('filter.language', $app->getLanguageFilter());

		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter-search', '', 'STRING');
		$this->setState('filter.search', $search);

		parent::populateState('title', 'ASC');
	}

	/**
	 * Method to get a store id based on the model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string $id An identifier string to generate the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   12.2
	 */
	protected function getStoreId($id = '')
	{
		// Add the wbi id to the store id.
		$id .= ':' . $this->getState('wbig.id');

		return parent::getStoreId($id);
	}

	/**
	 * Method to get category data for the current category
	 *
	 * @param    int        An optional ID
	 *
	 * @return    object
	 * @since    1.5
	 */
	public function getCategory()
	{
		if (!is_object($this->_item))
		{
			if (isset($this->state->params))
			{
				$params                = $this->state->params;
				$options               = array();
				$options['countItems'] = $params->get('show_cat_num_items', 1) || !$params->get('show_empty_categories', 0);
			}
			else
			{
				$options['countItems'] = 0;
			}
			$options['table'] = '#__wissensmatrix_mitarbeiter';

			$categories  = JCategories::getInstance('Wissensmatrix', $options);
			$this->_item = $categories->get($this->getState('category.id', 'root'));

			// Compute selected asset permissions.
			if (is_object($this->_item))
			{
				$user   = JFactory::getUser();
				$userId = $user->get('id');
				$asset  = 'com_wissensmatrix.category.' . $this->_item->id;

				// Check general create permission.
				if ($user->authorise('core.create', $asset))
				{
					$this->_item->getParams()->set('access-create', true);
				}

				// TODO: Why aren't we lazy loading the children and siblings?
				$this->_children = $this->_item->getChildren();
				$this->_parent   = false;

				if ($this->_item->getParent())
				{
					$this->_parent = $this->_item->getParent();
				}

				$this->_rightsibling = $this->_item->getSibling();
				$this->_leftsibling  = $this->_item->getSibling(false);
			}
			else
			{
				$this->_children = false;
				$this->_parent   = false;
			}
		}

		return $this->_item;
	}

	/**
	 * Get the parent categorie.
	 *
	 * @param    int        An optional category id. If not supplied, the model state 'category.id' will be used.
	 *
	 * @return    mixed    An array of categories or false if an error occurs.
	 * @since    1.6
	 */
	public function getParent()
	{
		if (!is_object($this->_item))
		{
			$this->getCategory();
		}

		return $this->_parent;
	}

	/**
	 * Get the left sibling (adjacent) categories.
	 *
	 * @return    mixed    An array of categories or false if an error occurs.
	 * @since    1.6
	 */
	function &getLeftSibling()
	{
		if (!is_object($this->_item))
		{
			$this->getCategory();
		}

		return $this->_leftsibling;
	}

	/**
	 * Get the right sibling (adjacent) categories.
	 *
	 * @return    mixed    An array of categories or false if an error occurs.
	 * @since    1.6
	 */
	function &getRightSibling()
	{
		if (!is_object($this->_item))
		{
			$this->getCategory();
		}

		return $this->_rightsibling;
	}

	/**
	 * Get the child categories.
	 *
	 * @param    int        An optional category id. If not supplied, the model state 'category.id' will be used.
	 *
	 * @return    mixed    An array of categories or false if an error occurs.
	 * @since    1.6
	 */
	function &getChildren()
	{
		if (!is_object($this->_item))
		{
			$this->getCategory();
		}

		// Order subcategories
		if (sizeof($this->_children))
		{
			$params = $this->getState()->get('params');
			if ($params->get('orderby_pri') == 'alpha' || $params->get('orderby_pri') == 'ralpha')
			{
				jimport('joomla.utilities.arrayhelper');
				JArrayHelper::sortObjects($this->_children, 'title', ($params->get('orderby_pri') == 'alpha') ? 1 : -1);
			}
		}

		return $this->_children;
	}
}