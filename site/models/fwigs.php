<?php
defined('_JEXEC') or die;

class WissensmatrixModelFwigs extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'fwigs.id',
				'fwigs.title_de', 'fwigs.title_fr', 'fwigs.title_it', 
				'title', 'title_de', 'title_fr', 'title_it', 
				'alias', 'fwigs.alias',
				'checked_out', 'fwigs.checked_out',
				'checked_out_time', 'fwigs.checked_out_time',
				'catid', 'fwigs.catid', 'category_title',
				'state', 'fwigs.state',
				'access', 'fwigs.access', 'access_level',
				'created', 'fwigs.created',
				'created_by', 'fwigs.created_by',
				'ordering', 'fwigs.ordering',
				'language', 'fwigs.language',
				'hits', 'fwigs.hits',
				'bool', 'fwigs.bool',
			);
		}

		parent::__construct($config);
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
		$jinput	= $app->input;

		// Category filter (priority on request so subcategories work)
		// Team in this case, not used but selection has to be saved in userstate
		$teamid = $this->getUserStateFromRequest('com_wissensmatrix.team.id', 'teamid', $params->get('teamid', 0), 'int');
		$this->setState('team.id', $teamid);

		// Category filter (priority on request so subcategories work)
		// We don't use userstate here as the category is not used as team but category.
		$id	= $jinput->get('catid', $params->get('catid', 0), 'int');
		$this->setState('category.id', $id);

		// Include Subcategories or not
		$this->setState('filter.subcategories', $params->get('show_subcategory_content', 0));

		$user	= JFactory::getUser();
		if ((!$user->authorise('core.edit.state', 'com_wissensmatrix')) &&  (!$user->authorise('core.edit', 'com_wissensmatrix'))){
			// filter on published for those who do not have edit or edit.state rights.
			$this->setState('filter.state', 1);
		}

		$this->setState('filter.language', $app->getLanguageFilter());

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter-search', '', 'STRING');
		$this->setState('filter.search', $search);

		parent::populateState('title', 'ASC');

		// Reset limits from parent function
		$this->setState('list.start', 0);
		$this->setState('list.limit', 0);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		$user	= JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());

		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'fwigs.id, fwigs.catid, fwigs.language, '.
				'CASE WHEN CHAR_LENGTH(fwigs.alias) THEN CONCAT_WS(\':\', fwigs.id, fwigs.alias) ELSE fwigs.id END as slug, ' .
				'fwigs.checked_out, fwigs.checked_out_time, '.
				'fwigs.alias, fwigs.created, fwigs.created_by, '.
				'fwigs.state, fwigs.bool, fwigs.ordering, fwigs.hits'
			)
		);
		$query->from('`#__wissensmatrix_fachwissengruppe` AS fwigs');

		// Create title from active language
		$lang	= substr(JFactory::getLanguage()->getTag(), 0, 2);
		$query->select('fwigs.`title_'.$lang.'` AS title');

		// Join over Fwigs Category.
		$query->select('c_fwigs.title AS category_title');
		$query->select('CASE WHEN CHAR_LENGTH(c_fwigs.alias) THEN CONCAT_WS(\':\', c_fwigs.id, c_fwigs.alias) ELSE c_fwigs.id END as catslug');
		$query->join('LEFT', '#__categories AS c_fwigs ON c_fwigs.id = fwigs.catid');
		$query->where('(fwigs.catid = 0 OR (c_fwigs.access IN ('.$groups.') AND c_fwigs.published = 1))');

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
				$subQuery->where('this.id = '.(int) $categoryId);
				if ($levels > 0) {
					$subQuery->where('sub.level <= this.level + '.$levels);
				}
				// Add the subquery to the main query
				$query->where('(fwigs.catid = '.(int) $categoryId
					.' OR fwigs.catid IN ('.$subQuery->__toString().'))');
			}
			else
			{
				$query->where('fwigs.catid = '.(int) $categoryId);
			}
		}

		// Join over users for the author names.
		$query->select("user.name AS author");
		$query->join('LEFT', '#__users AS user ON user.id = fwigs.created_by');

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			$search = $db->Quote('%'.$db->escape($search, true).'%');
			$query->where('(fwigs.title_'.$lang.' LIKE '.$search.')');
		}

		// Filter by state
		$state = $this->getState('filter.state');
		if (is_numeric($state))
		{
			$query->where('fwigs.state = '.(int) $state);
		}

		// Filter by language
		if ($this->getState('filter.language'))
		{
			$query->where('fwigs.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')');
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
		if ($orderCol == 'fwigs.ordering' || $orderCol == 'category_title') {
			$orderCol = 'category_title '.$orderDirn.', fwigs.ordering';
		}
		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
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
				$options = array();
				$options['countItems'] = $params->get('show_cat_num_items', 1) || !$params->get('show_empty_categories', 0);
			}
			else
			{
				$options['countItems'] = 0;
			}
			$options['table'] = '#__wissensmatrix_mitarbeiter';

			$categories = JCategories::getInstance('Wissensmatrix', $options);
			$this->_item = $categories->get($this->getState('category.id', 'root'));

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