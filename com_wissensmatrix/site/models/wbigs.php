<?php
defined('_JEXEC') or die;

class WissensmatrixModelWbigs extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param    array    An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'wbigs.id',
				'wbigs.title_de', 'wbigs.title_fr', 'wbigs.title_it',
				'title', 'title_de', 'title_fr', 'title_it',
				'sbb_nr', 'wbigs.sbb_nr',
				'refresh', 'wbigs.refresh',
				'alias', 'wbigs.alias',
				'checked_out', 'wbigs.checked_out',
				'checked_out_time', 'wbigs.checked_out_time',
				'catid', 'wbigs.catid', 'category_title',
				'state', 'wbigs.state',
				'access', 'wbigs.access', 'access_level',
				'created', 'wbigs.created',
				'created_by', 'wbigs.created_by',
				'ordering', 'wbigs.ordering',
				'language', 'wbigs.language',
				'hits', 'wbigs.hits',
			);
		}

		parent::__construct($config);
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
		// Initialise variables.
		$app    = JFactory::getApplication();
		$params = $app->getParams();
		$this->setState('params', $params);
		$jinput = $app->input;

		// Category filter (priority on request so subcategories work)
		// Team in this case, not used but selection has to be saved in userstate
		$teamid = $this->getUserStateFromRequest('com_wissensmatrix.team.id', 'teamid', $params->get('teamid', 0), 'int');
		$this->setState('team.id', $teamid);

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter-search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		$categoryId = $app->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id', '');
		$this->setState('filter.category_id', $categoryId);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// List state information.
		parent::populateState('title', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param    string $id A prefix for the store id.
	 *
	 * @return    string        A store id.
	 * @since    1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.category_id');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return    JDatabaseQuery
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'wbigs.id, wbigs.catid, wbigs.language, ' .
				'CASE WHEN CHAR_LENGTH(wbigs.alias) THEN CONCAT_WS(\':\', wbigs.id, wbigs.alias) ELSE wbigs.id END as slug, ' .
				'wbigs.checked_out, wbigs.checked_out_time, ' .
				'wbigs.alias, wbigs.created, wbigs.created_by, ' .
				'wbigs.state, wbigs.ordering, wbigs.hits'
			)
		);
		$query->from('`#__wissensmatrix_weiterbildunggruppe` AS wbigs');

		// Create title from active language
		$lang = substr(JFactory::getLanguage()->getTag(), 0, 2);
		$query->select('wbigs.`title_' . $lang . '` AS title');

		// Join over the language
		$query->select('l.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = wbigs.language');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = wbigs.checked_out');

		// Join over the categories.
		$query->select('c.title AS category_title');
		$query->join('LEFT', '#__categories AS c ON c.id = wbigs.catid');
		$query->where('(wbigs.catid = 0 OR (c.access IN (' . $groups . ') AND c.published = 1))');

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published))
		{
			$query->where('wbigs.state = ' . (int) $published);
		}
		else if ($published === '')
		{
			$query->where('(wbigs.state IN (0, 1))');
		}

		// Filter by category.
		$categoryId = $this->getState('filter.category_id');
		if (is_numeric($categoryId))
		{
			$query->where('wbigs.catid = ' . (int) $categoryId);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('wbigs.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(wbigs.title_' . $lang . ' LIKE ' . $search . ')');
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where('wbigs.language = ' . $db->quote($language));
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		if ($orderCol == 'wbigs.ordering' || $orderCol == 'category_title')
		{
			$orderCol = 'category_title ' . $orderDirn . ', wbigs.ordering';
		}
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}
}