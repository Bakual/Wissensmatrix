<?php
defined('_JEXEC') or die;

class WissensmatrixModelWorkers extends JModelList
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
				'id', 'workers.id',
				'uid', 'workers.uid',
				'name', 'workers.name', 
				'vorname', 'workers.vorname', 
				'alias', 'workers.alias',
				'checked_out', 'workers.checked_out',
				'checked_out_time', 'workers.checked_out_time',
				'catid', 'workers.catid', 'category_title',
				'state', 'workers.state',
				'access', 'workers.access', 'access_level',
				'created', 'workers.created',
				'created_by', 'workers.created_by',
				'ordering', 'workers.ordering',
				'hits', 'workers.hits',
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
		// Initialise variables.
		$app = JFactory::getApplication();

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		$categoryId = $app->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id', '');
		$this->setState('filter.category_id', $categoryId);

		// Load the parameters.
		$params	= JComponentHelper::getParams('com_wissensmatrix');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('workers.ordering', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.state');
		$id.= ':' . $this->getState('filter.category_id');
		$id.= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'workers.id, workers.hits, '.
				'workers.name, workers.vorname, workers.language, '.
				'workers.uid, workers.catid, '.
				'workers.checked_out, workers.checked_out_time, '.
				'workers.alias, workers.created, workers.created_by, '.
				'workers.state, workers.ordering'
			)
		);
		$query->from('`#__wissensmatrix_mitarbeiter` AS workers');

		// Join over the language
		$query->select('l.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages').' AS l ON l.lang_code = workers.language');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = workers.checked_out');

		// Join over the categories.
		$query->select('c.title AS category_title');
		$query->join('LEFT', '#__categories AS c ON c.id = workers.catid');

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('workers.state = '.(int) $published);
		} else if ($published === '') {
			$query->where('(workers.state IN (0, 1))');
		}

		// Filter by category.
		$categoryId = $this->getState('filter.category_id');
		if (is_numeric($categoryId)) {
			$query->where('workers.catid = '.(int) $categoryId);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('workers.id = '.(int) substr($search, 3));
			} elseif (stripos($search, 'uid:') === 0) {
				$query->where('workers.uid = '.(int) substr($search, 4));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('((workers.name LIKE '.$search.') OR (workers.vorname LIKE '.$search.'))');
			}
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
		if ($orderCol == 'workers.ordering' || $orderCol == 'category_title') {
			$orderCol = 'category_title '.$orderDirn.', workers.ordering';
		}
		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}
}