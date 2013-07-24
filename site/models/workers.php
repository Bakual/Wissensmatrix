<?php
/**
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * @package		Wissensmatrix
 */
// Based on com_contact
class WissensmatrixModelWorkers extends JModelList
{
	protected $_item= null;
	protected $_siblings = null;
	protected $_children = null;
	protected $_parent = null;

	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'vorname', 'workers.vorname',
				'name', 'workers.name',
				'ordering', 'workers.ordering',
				'checked_out', 'workers.checked_out',
				'checked_out_time', 'workers.checked_out_time',
				'language', 'workers.language',
				'hits', 'workers.hits',
				'category_title', 'c_workers.category_title',
			);
		}

		parent::__construct($config);
	}

	protected function getListQuery()
	{
		$user	= JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());

		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'workers.id, workers.catid, ' .
				'workers.vorname, workers.name, ' .
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
		$query->where('(workers.catid = 0 OR (c_workers.access IN ('.$groups.') AND c_workers.published = 1))');

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
				$subQuery->where('this.id = '.(int) $categoryId);
				if ($levels > 0) {
					$subQuery->where('sub.level <= this.level + '.$levels);
				}
				// Add the subquery to the main query
				$query->where('(workers.catid = '.(int) $categoryId
					.' OR workers.catid IN ('.$subQuery->__toString().'))');
			}
			else
			{
				$query->where('workers.catid = '.(int) $categoryId);
			}
		}

		// Join over users for the author names.
		$query->select("user.name AS author");
		$query->join('LEFT', '#__users AS user ON user.id = workers.created_by');

		// Filter by search in name
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->Quote('%'.$db->escape($search, true).'%');
			$query->where('(workers.name LIKE '.$search.') OR (workers.vorname LIKE '.$search.')');
		}

		// Filter by state
		$state = $this->getState('filter.state');
		if (is_numeric($state)) {
			$query->where('workers.state = '.(int) $state);
		}

		// Filter by language
		if ($this->getState('filter.language')) {
			$query->where('workers.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')');
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'ordering')).' '.$db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app	= JFactory::getApplication();
		$params	= $app->getParams();
		$this->setState('params', $params);

		// Category filter (priority on request so subcategories work)
		// Team in this case
		$teamid = $this->getUserStateFromRequest('com_wissensmatrix.team.id', 'teamid', $params->get('teamid', 0), 'int');
		$this->setState('team.id', $teamid);

		// Include Subcategories or not
		$this->setState('filter.subcategories', $params->get('show_subcategory_content', 0));

		$user	= JFactory::getUser();
		if ((!$user->authorise('core.edit.state', 'com_wissensmatrix')) &&  (!$user->authorise('core.edit', 'com_wissensmatrix'))){
			// filter on published for those who do not have edit or edit.state rights.
			$this->setState('filter.state', 1);
		}

		$this->setState('filter.language', $app->getLanguageFilter());

		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter-search', '', 'STRING');
		$this->setState('filter.search', $search);

		parent::populateState('ordering', 'ASC');
	}

	/**
	 * Method to get category data for the current category
	 *
	 * @param	int		An optional ID
	 *
	 * @return	object
	 * @since	1.5
	 */
	public function getCategory()
	{
		if (!is_object($this->_item))
		{
			if(isset($this->state->params))
			{
				$params = $this->state->params;
				$options['countItems'] = $params->get('show_cat_num_items', 1) || !$params->get('show_empty_categories', 0);
			}
			else
			{
				$options['countItems'] = 0;
			}
			$options['table'] = '#__wissensmatrix_mitarbeiter';

			$categories = JCategories::getInstance('Wissensmatrix', $options);
			$this->_item = $categories->get($this->getState('team.id', 'root')); // use team.id here

			// Compute selected asset permissions.
			if (is_object($this->_item)) {
				$user	= JFactory::getUser();
				$userId	= $user->get('id');
				$asset	= 'com_wissensmatrix.category.'.$this->_item->id;

				// Check general create permission.
				if ($user->authorise('core.create', $asset)) {
					$this->_item->getParams()->set('access-create', true);
				}

				// TODO: Why aren't we lazy loading the children and siblings?
				$this->_children = $this->_item->getChildren();
				$this->_parent = false;

				if ($this->_item->getParent()) {
					$this->_parent = $this->_item->getParent();
				}

				$this->_rightsibling = $this->_item->getSibling();
				$this->_leftsibling = $this->_item->getSibling(false);
			}
			else {
				$this->_children = false;
				$this->_parent = false;
			}
		}

		return $this->_item;
	}

	/**
	 * Get the parent categorie.
	 *
	 * @param	int		An optional category id. If not supplied, the model state 'category.id' will be used.
	 *
	 * @return	mixed	An array of categories or false if an error occurs.
	 * @since	1.6
	 */
	public function getParent()
	{
		if (!is_object($this->_item)) {
			$this->getCategory();
		}

		return $this->_parent;
	}

	/**
	 * Get the left sibling (adjacent) categories.
	 *
	 * @return	mixed	An array of categories or false if an error occurs.
	 * @since	1.6
	 */
	function &getLeftSibling()
	{
		if (!is_object($this->_item)) {
			$this->getCategory();
		}

		return $this->_leftsibling;
	}

	/**
	 * Get the right sibling (adjacent) categories.
	 *
	 * @return	mixed	An array of categories or false if an error occurs.
	 * @since	1.6
	 */
	function &getRightSibling()
	{
		if (!is_object($this->_item)) {
			$this->getCategory();
		}

		return $this->_rightsibling;
	}

	/**
	 * Get the child categories.
	 *
	 * @param	int		An optional category id. If not supplied, the model state 'category.id' will be used.
	 *
	 * @return	mixed	An array of categories or false if an error occurs.
	 * @since	1.6
	 */
	function &getChildren()
	{
		if (!is_object($this->_item)) {
			$this->getCategory();
		}

		// Order subcategories
		if (sizeof($this->_children)) {
			$params = $this->getState()->get('params');
			if ($params->get('orderby_pri') == 'alpha' || $params->get('orderby_pri') == 'ralpha') {
				jimport('joomla.utilities.arrayhelper');
				JArrayHelper::sortObjects($this->_children, 'title', ($params->get('orderby_pri') == 'alpha') ? 1 : -1);
			}
		}

		return $this->_children;
	}
}