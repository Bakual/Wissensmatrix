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
		// Initialise variables.
		$app = JFactory::getApplication();

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		$bool = $app->getUserStateFromRequest($this->context.'.filter.bool', 'filter_bool', '', 'string');
		$this->setState('filter.bool', $bool);

		$categoryId = $app->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id', '');
		$this->setState('filter.category_id', $categoryId);

		$language = $this->getUserStateFromRequest($this->context.'.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_wissensmatrix');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('fwigs.ordering', 'asc');
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
				'fwigs.id, fwigs.catid, fwigs.language, '.
				'fwigs.checked_out, fwigs.checked_out_time, '.
				'fwigs.alias, fwigs.created, fwigs.created_by, '.
				'fwigs.state, fwigs.bool, fwigs.ordering, fwigs.hits'
			)
		);
		$query->from('`#__wissensmatrix_fachwissengruppe` AS fwigs');

		// Create title from active language
		$lang	= substr(JFactory::getLanguage()->getTag(), 0, 2);
		$query->select('fwigs.`title_'.$lang.'` AS title');

		// Join over the language
		$query->select('l.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages').' AS l ON l.lang_code = fwigs.language');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = fwigs.checked_out');

		// Join over the categories.
		$query->select('c.title AS category_title');
		$query->join('LEFT', '#__categories AS c ON c.id = fwigs.catid');

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('fwigs.state = '.(int) $published);
		} else if ($published === '') {
			$query->where('(fwigs.state IN (0, 1))');
		}

		// Filter by bool.
		$bool = $this->getState('filter.bool');
		if (is_numeric($bool)) {
			$query->where('fwigs.bool = '.(int) $bool);
		}

		// Filter by category.
		$categoryId = $this->getState('filter.category_id');
		if (is_numeric($categoryId)) {
			$query->where('fwigs.catid = '.(int) $categoryId);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('fwigs.id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('(fwigs.title_'.$lang.' LIKE '.$search.')');
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language')) {
			$query->where('fwigs.language = '.$db->quote($language));
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
}