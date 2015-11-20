<?php
/**
 * @copyright      Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * @package        Wissensmatrix
 */
// Based on com_contact
class WissensmatrixModelWorkers extends JModelList
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
				'vorname', 'workers.vorname',
				'name', 'workers.name',
				'ordering', 'workers.ordering',
				'checked_out', 'workers.checked_out',
				'checked_out_time', 'workers.checked_out_time',
				'language', 'workers.language',
				'hits', 'workers.hits',
				'category_title', 'c_workers.category_title',
				'zwbi_status_id', 'date', 'zwbi_refresh',
				'refresh',
				'responsibility', 'fwi_title'
			);
		}

		parent::__construct($config);
	}

	protected function getListQuery()
	{
		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$catids = $user->getAuthorisedCategories('com_wissensmatrix', 'wissensmatrix.view.worker');
		$catids = implode(',', $catids);

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'workers.id, workers.catid, ' .
				'workers.vorname, workers.name, workers.uid, ' .
				'CASE WHEN CHAR_LENGTH(workers.alias) THEN CONCAT_WS(\':\', workers.id, workers.alias) ELSE workers.id END as slug, ' .
				'workers.hits, workers.alias, workers.checked_out, workers.checked_out_time,' .
				'workers.state, workers.ordering, workers.created, workers.created_by'
			)
		);
		$query->from('`#__wissensmatrix_mitarbeiter` AS workers');

		// Join over Workers Category.
		$query->select('c_workers.title AS category_title');
		$query->select('CASE WHEN CHAR_LENGTH(c_workers.alias) THEN CONCAT_WS(\':\', c_workers.id, c_workers.alias) ELSE c_workers.id END as catslug');
		$query->join('LEFT', '#__categories AS c_workers ON c_workers.id = workers.catid');
		$query->where('c_workers.published = 1');
		$query->where('c_workers.access IN (' . $groups . ')');
		$query->where('workers.catid IN (' . $catids . ')');

		// Filter by category
		if ($categoryId = $this->getState('team.id'))
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
				$query->where('(workers.catid = ' . (int) $categoryId
					. ' OR workers.catid IN (' . $subQuery->__toString() . '))');
			}
			else
			{
				$query->where('workers.catid = ' . (int) $categoryId);
			}
		}

		// Join over users for the author names.
		$query->select("user.name AS author");
		$query->join('LEFT', '#__users AS user ON user.id = workers.created_by');

		// Filter by search in name
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');
			$query->where('((workers.name LIKE ' . $search . ') OR (workers.vorname LIKE ' . $search . '))');
		}

		// Filter by state
		$state = $this->getState('filter.state');
		if (is_numeric($state))
		{
			$query->where('workers.state = ' . (int) $state);
		}
		else
		{
			$query->where('workers.state IN (0,1)');
		}

		// Extend query for responsibilesfwi report)
		if ($this->getState('filter.responsible'))
		{
			// Join the zfwi table
			$query->join('LEFT', '#__wissensmatrix_mit_fwi AS zfwi ON zfwi.mit_id = workers.id');
			$query->select('zfwi.responsibility');
			$query->where('zfwi.responsibility > 0');

			// Create title from active language
			$lang = substr(JFactory::getLanguage()->getTag(), 0, 2);

			// Join the fwi table
			$query->join('LEFT', '#__wissensmatrix_fachwissen AS fwis ON zfwi.fwi_id = fwis.id');
			$query->select('fwis.`title_' . $lang . '` AS fwi_title');
			$query->select('fwis.`id` AS fwi_id');

			// Join the fwig table
			$query->join('LEFT', '#__wissensmatrix_fachwissengruppe AS fwigs ON fwis.fwig_id = fwigs.id');
			$query->select('fwigs.`title_' . $lang . '` AS fwig_title');

			if ($fwi_id = (int) $this->getState('filter.fwi_id'))
			{
				$query->where('zfwi.fwi_id = ' . $fwi_id);
			}
		}

		// Filter by wbi (needed in wbi reports)
		if ($wbiId = $this->getState('wbi.id'))
		{
			$query->select('zwbi.id as zwbi_id, zwbi.status_id as zwbi_status_id, zwbi.bemerkung, zwbi.date, mit_id');
			$query->join('LEFT', '#__wissensmatrix_mit_wbi AS zwbi ON zwbi.mit_id = workers.id');
			$query->where('wbi_id = ' . (int) $wbiId);
			if ($zwbistate = $this->getState('filter.zwbistate'))
			{
				$query->where('zwbi.status_id = ' . (int) $zwbistate);
			}
			$query->select('wbis.refresh');
			$query->select('CASE WHEN wbis.refresh THEN DATE_ADD(zwbi.date, INTERVAL wbis.refresh YEAR) ELSE 0 END as zwbi_refresh');
			$query->join('LEFT', '#__wissensmatrix_weiterbildung AS wbis ON zwbi.wbi_id = wbis.id');
		}
		else
		{
			// Reset ordering if invalid
			$invalid = array('zwbi_status_id', 'date', 'refresh', 'zwbi_refresh');
			if (in_array($this->getState('list.ordering', 'ordering'), $invalid))
			{
				$this->setState('list.ordering', 'ordering');
			}
		}

		// Filter by language
		if ($this->getState('filter.language'))
		{
			$query->where('workers.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
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
		$user = JFactory::getUser();

		// Category filter (priority on request so subcategories work)
		// Team in this case
		$teamid = $this->getUserStateFromRequest('com_wissensmatrix.team.id', 'teamid', null, 'int');

		// Check default team
		if ($teamid === null)
		{
			$teamid = WissensmatrixHelperWissensmatrix::getDefaultTeam();
			JFactory::getApplication()->setUserState('com_wissensmatrix.team.id', $teamid);
		}

		$this->setState('team.id', $teamid);

		$zwbistate = $app->getUserStateFromRequest($this->context . '.filter.zwbistate', 'zwbistate', 0, 'INT');
		$this->setState('filter.zwbistate', $zwbistate);

		// Include Subcategories or not
		$this->setState('filter.subcategories', $params->get('show_subcategory_content', 0));

		if ((!$user->authorise('core.edit.state', 'com_wissensmatrix')) && (!$user->authorise('core.edit', 'com_wissensmatrix')))
		{
			// filter on published for those who do not have edit or edit.state rights.
			$this->setState('filter.state', 1);
		}

		$this->setState('filter.language', $app->getLanguageFilter());

		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter-search', '', 'STRING');
		$this->setState('filter.search', $search);

		parent::populateState('ordering', 'ASC');
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
		$id .= ':' . $this->getState('wbi.id');

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
				$options['countItems'] = $params->get('show_cat_num_items', 1) || !$params->get('show_empty_categories', 0);
			}
			else
			{
				$options['countItems'] = 0;
			}
			$options['table'] = '#__wissensmatrix_mitarbeiter';

			$categories  = JCategories::getInstance('Wissensmatrix', $options);
			$this->_item = $categories->get($this->getState('team.id', 'root')); // use team.id here

			// Compute selected asset permissions.
			if (is_object($this->_item))
			{
				$user   = JFactory::getUser();
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