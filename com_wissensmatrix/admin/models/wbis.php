<?php
defined('_JEXEC') or die;

class WissensmatrixModelWbis extends JModelList
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
				'id', 'wbis.id',
				'wbis.title_de', 'wbis.title_fr', 'wbis.title_it',
				'title', 'title_de', 'title_fr', 'title_it',
				'sbb_nr', 'wbis.sbb_nr',
				'refresh', 'wbis.refresh',
				'alias', 'wbis.alias',
				'checked_out', 'wbis.checked_out',
				'checked_out_time', 'wbis.checked_out_time',
				'catid', 'wbis.catid', 'category_title',
				'state', 'wbis.state',
				'access', 'wbis.access', 'access_level',
				'created', 'wbis.created',
				'created_by', 'wbis.created_by',
				'ordering', 'wbis.ordering',
				'language', 'wbis.language',
				'hits', 'wbis.hits',
				'wbig_title',
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
		$app = JFactory::getApplication();

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		$wbig = $app->getUserStateFromRequest($this->context . '.filter.wbig', 'filter_wbig', '', 'string');
		$this->setState('filter.wbig', $wbig);

		$categoryId = $app->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id', '');
		$this->setState('filter.category_id', $categoryId);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_wissensmatrix');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('wbis.ordering', 'asc');
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
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'wbis.id, wbis.catid, wbis.language, ' .
				'wbis.checked_out, wbis.checked_out_time, ' .
				'wbis.alias, wbis.created, wbis.created_by, ' .
				'wbis.state, wbis.ordering, wbis.hits'
			)
		);
		$query->from('`#__wissensmatrix_weiterbildung` AS wbis');

		// Create title from active language
		$lang = substr(JFactory::getLanguage()->getTag(), 0, 2);
		$query->select('wbis.`title_' . $lang . '` AS title');

		// Join over the language
		$query->select('l.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = wbis.language');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = wbis.checked_out');

		// Join over the wbigs.
		$query->select('wbigs.title_' . $lang . ' AS wbig_title');
		$query->join('LEFT', '#__wissensmatrix_weiterbildunggruppe AS wbigs ON wbigs.id = wbis.wbig_id');

		// Join over the categories.
		$query->select('c.title AS category_title');
		$query->join('LEFT', '#__categories AS c ON c.id = wbis.catid');

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published))
		{
			$query->where('wbis.state = ' . (int) $published);
		}
		else if ($published === '')
		{
			$query->where('(wbis.state IN (0, 1))');
		}

		// Filter by wbig
		$wbig = $this->getState('filter.wbig');
		if (is_numeric($wbig))
		{
			$query->where('wbis.wbig_id = ' . (int) $wbig);
		}

		// Filter by category.
		$categoryId = $this->getState('filter.category_id');
		if (is_numeric($categoryId))
		{
			$query->where('wbis.catid = ' . (int) $categoryId);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('wbis.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(wbis.title_' . $lang . ' LIKE ' . $search . ')');
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where('wbis.language = ' . $db->quote($language));
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		if ($orderCol == 'wbis.ordering' || $orderCol == 'category_title')
		{
			$orderCol = 'category_title ' . $orderDirn . ', wbis.ordering';
		}
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	public function getWbigs()
	{
		// Initialize variables.
		$options = array();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id AS value, title_de AS text');
		$query->from('#__wissensmatrix_weiterbildunggruppe');
		$query->where('state = 1');
		$query->order('title_de');

		// Get the options.
		$db->setQuery($query);

		$options = $db->loadObjectList();

		return $options;
	}
}