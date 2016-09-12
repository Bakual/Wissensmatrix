<?php
defined('_JEXEC') or die;

class WissensmatrixModelFwis extends JModelList
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
				'id', 'fwis.id',
				'fwis.title_de', 'fwis.title_fr', 'fwis.title_it',
				'title', 'title_de', 'title_fr', 'title_it',
				'alias', 'fwis.alias',
				'checked_out', 'fwis.checked_out',
				'checked_out_time', 'fwis.checked_out_time',
				'catid', 'fwis.catid', 'category_title',
				'state', 'fwis.state',
				'access', 'fwis.access', 'access_level',
				'created', 'fwis.created',
				'created_by', 'fwis.created_by',
				'ordering', 'fwis.ordering',
				'language', 'fwis.language',
				'hits', 'fwis.hits',
				'snow', 'fwis.snow',
				'fwig_title',
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

		$fwig = $app->getUserStateFromRequest($this->context . '.filter.fwig', 'filter_fwig', '', 'string');
		$this->setState('filter.fwig', $fwig);

		$snow = $app->getUserStateFromRequest($this->context . '.filter.snow', 'filter_snow', '', 'string');
		$this->setState('filter.snow', $snow);

		$categoryId = $app->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id', '');
		$this->setState('filter.category_id', $categoryId);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_wissensmatrix');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('fwis.ordering', 'asc');
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
				'fwis.id, fwis.catid, fwis.language, ' .
				'fwis.checked_out, fwis.checked_out_time, ' .
				'fwis.alias, fwis.created, fwis.created_by, ' .
				'fwis.state, fwis.ordering, fwis.hits, fwis.snow'
			)
		);
		$query->from('`#__wissensmatrix_fachwissen` AS fwis');

		// Create title from active language
		$lang = substr(JFactory::getLanguage()->getTag(), 0, 2);
		$query->select('IF (CHAR_LENGTH(fwis.`title_' . $lang . '`) > 75, CONCAT(LEFT(fwis.`title_' . $lang . '`, 72), "..."), fwis.`title_' . $lang . '`) AS title');

		// Join over the language
		$query->select('l.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = fwis.language');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = fwis.checked_out');

		// Join over the fwigs.
		$query->select('fwigs.title_' . $lang . ' AS fwig_title');
		$query->join('LEFT', '#__wissensmatrix_fachwissengruppe AS fwigs ON fwigs.id = fwis.fwig_id');

		// Join over the categories.
		$query->select('c.title AS category_title');
		$query->join('LEFT', '#__categories AS c ON c.id = fwis.catid');

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published))
		{
			$query->where('fwis.state = ' . (int) $published);
		}
		else if ($published === '')
		{
			$query->where('(fwis.state IN (0, 1))');
		}

		// Filter by fwig
		$fwig = $this->getState('filter.fwig');
		if (is_numeric($fwig))
		{
			$query->where('fwis.fwig_id = ' . (int) $fwig);
		}

		// Filter by snow.
		$snow = $this->getState('filter.snow');
		if (is_numeric($snow))
		{
			$query->where('fwigs.snow = ' . (int) $snow);
		}

		// Filter by category.
		$categoryId = $this->getState('filter.category_id');
		if (is_numeric($categoryId))
		{
			$query->where('fwis.catid = ' . (int) $categoryId);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('fwis.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(fwis.title_' . $lang . ' LIKE ' . $search . ')');
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where('fwis.language = ' . $db->quote($language));
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		if ($orderCol == 'fwis.ordering' || $orderCol == 'category_title')
		{
			$orderCol = 'category_title ' . $orderDirn . ', fwis.ordering';
		}
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	public function getFwigs()
	{
		// Initialize variables.
		$options = array();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id AS value, title_de AS text');
		$query->from('#__wissensmatrix_fachwissengruppe');
		$query->order('title_de');

		// Get the options.
		$db->setQuery($query);

		$options = $db->loadObjectList();

		return $options;
	}
}