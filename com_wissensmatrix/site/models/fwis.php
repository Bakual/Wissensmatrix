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
class WissensmatrixModelFwis extends JModelList
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
				'fwis.title_de', 'fwis.title_fr', 'fwis.title_it',
				'title', 'title_de', 'title_fr', 'title_it',
				'ordering', 'fwis.ordering',
				'checked_out', 'fwis.checked_out',
				'checked_out_time', 'fwis.checked_out_time',
				'language', 'fwis.language',
				'hits', 'fwis.hits',
				'category_title', 'c_fwis.category_title',
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
				'fwis.id, fwis.catid, ' .
				'CASE WHEN CHAR_LENGTH(fwis.alias) THEN CONCAT_WS(\':\', fwis.id, fwis.alias) ELSE fwis.id END as slug, ' .
				'fwis.hits, fwis.alias, fwis.checked_out, fwis.checked_out_time,' .
				'fwis.state, fwis.ordering, fwis.created, fwis.created_by'
			)
		);

		$query->from('`#__wissensmatrix_fachwissen` AS fwis');

		// Create title from active language
		$lang = substr(JFactory::getLanguage()->getTag(), 0, 2);
		$query->select('fwis.`title_' . $lang . '` AS title');

		// Join over Fwis Category.
		$query->select('c_fwis.title AS category_title');
		$query->select('CASE WHEN CHAR_LENGTH(c_fwis.alias) THEN CONCAT_WS(\':\', c_fwis.id, c_fwis.alias) ELSE c_fwis.id END as catslug');
		$query->join('LEFT', '#__categories AS c_fwis ON c_fwis.id = fwis.catid');
		$query->where('c_fwis.access IN (' . $groups . ')');
		$query->where('c_fwis.published = 1');

		// Join over Fwigs.
		$query->select('fwigs.title_' . $lang . ' AS fwig_title, fwigs.id AS fwig_id');
		$query->select('CASE WHEN CHAR_LENGTH(fwigs.alias) THEN CONCAT_WS(\':\', fwigs.id, fwigs.alias) ELSE fwigs.id END as fwigslug');
		$query->join('LEFT', '#__wissensmatrix_fachwissengruppe AS fwigs ON fwigs.id = fwis.fwig_id');

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
				$query->where('(fwis.catid = ' . (int) $categoryId
					. ' OR fwis.catid IN (' . $subQuery->__toString() . '))');
			}
			else
			{
				$query->where('fwis.catid = ' . (int) $categoryId);
			}
		}

		// Join over users for the author names.
		$query->select("user.name AS author");
		$query->join('LEFT', '#__users AS user ON user.id = fwis.created_by');

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');
			$query->where('(fwis.title_' . $lang . ' LIKE ' . $search . ')');
		}

		// Filter by state
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('fwis.state = ' . (int) $state);
		}

		// Filter by worker (needed in worker view)
		if ($workerId = $this->getState('worker.id'))
		{
			$query->select('zfwi.id as zfwi_id, zfwi.mit_id');
			$query->select('ist_level.value as ist, ist_level.title as ist_title');
			$query->select('soll_level.value as soll, soll_level.title as soll_title');
			$query->select('zfwi.responsibility');
			$query->join('LEFT', '#__wissensmatrix_mit_fwi AS zfwi ON zfwi.fwi_id = fwis.id');
			$query->join('LEFT', '#__wissensmatrix_erfahrung AS ist_level ON zfwi.ist = ist_level.id');
			$query->join('LEFT', '#__wissensmatrix_erfahrung AS soll_level ON zfwi.soll = soll_level.id');
			$query->where('zfwi.mit_id = ' . (int) $workerId);
		}

		// Filter by fwig (needed in zfwig view)
		if ($fwigId = $this->getState('fwig.id'))
		{
			$query->where('fwigs.id = ' . (int) $fwigId);
		}

		// Filter by zfwi.state (needed in worker view)
		$zfwistate = $this->getState('filter.zfwistate');

		if (is_numeric($zfwistate))
		{
			$query->where('zfwi.state = ' . (int) $zfwistate);
		}

		// Filter by language
		if ($this->getState('filter.language'))
		{
			$query->where('fwis.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		// Add the list ordering clause.
		if ($workerId)
		{
			$query->order('fwig_title ASC, title ASC');
		}
		else
		{
			$orderCol  = $this->state->get('list.ordering');
			$orderDirn = $this->state->get('list.direction');

			if ($orderCol == 'fwigs.ordering' || $orderCol == 'category_title')
			{
				$orderCol = 'category_title ' . $orderDirn . ', fwigs.ordering';
			}

			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

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
			// filter on published for those who do not have edit or edit.state rights.
			$this->setState('filter.state', 1);
		}

		$this->setState('filter.language', $app->getLanguageFilter());

		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter-search', '', 'STRING');
		$this->setState('filter.search', $search);

		parent::populateState('title', 'ASC');
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
				$user  = JFactory::getUser();
				$asset = 'com_wissensmatrix.category.' . $this->_item->id;

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

	/**
	 * Get the ist and soll value for a given Fachwissen and Worker.
	 *
	 * @fwi        int        The id of the Fachwissen
	 * @mit        int        The id of the Worker
	 *
	 * @return    mixed    An array with ist, soll and template soll value.
	 * @since      3.0
	 */
	public function getIstSoll($fwi, $mit)
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select required fields from the table.
		$query->select('zfwis.ist as ist_id, ist_level.value as ist, ist_level.title as ist_title');
		$query->select('zfwis.soll as soll_id, soll_level.value as soll, soll_level.title as soll_title');
		$query->select('zfwis.responsibility');
		$query->from('`#__wissensmatrix_mit_fwi` AS zfwis');
		$query->join('LEFT', '#__wissensmatrix_erfahrung AS ist_level ON zfwis.ist = ist_level.id');
		$query->join('LEFT', '#__wissensmatrix_erfahrung AS soll_level ON zfwis.soll = soll_level.id');
		$query->where('zfwis.mit_id = ' . (int) $mit);
		$query->where('zfwis.fwi_id = ' . (int) $fwi);
		$db->setQuery($query);
		$item = $db->loadAssoc();

		if (!$item)
		{
			$item = array('ist' => 0, 'ist_id' => 0, 'ist_title' => '-', 'soll' => 0, 'soll_id' => 0, 'soll_title' => '-');
		}

		$query = $db->getQuery(true);
		$query->select('template_id');
		$query->from('`#__wissensmatrix_mitarbeiter`');
		$query->where('id = ' . (int) $mit);
		$db->setQuery($query);
		$template_id = $db->loadResult();

		$query = $db->getQuery(true);
		$query->select('zfwis.soll as template_id, soll_level.title as template');
		$query->from('`#__wissensmatrix_mit_fwi` as zfwis');
		$query->join('LEFT', '#__wissensmatrix_erfahrung AS soll_level ON zfwis.soll = soll_level.id');
		$query->where('fwi_id = ' . (int) $fwi);
		$query->where('mit_id = ' . (int) $template_id);
		$db->setQuery($query);
		$template            = $db->loadAssoc();
		$item['template_id'] = $template['template_id'];
		$item['template']    = $template['template'];

		return $item;
	}

	/**
	 * Counts the workers in a given Team, Fachwissen and Level.
	 *
	 * @fwi        int        The id of the Fachwissen
	 * @team       int        The id of the team (category)
	 * @erf        int        The id of the level
	 * @ist        bool    true for 'ist' and false for 'soll'
	 *
	 * @return    int        Count
	 * @since      3.0
	 */
	public function getWorkerCount($fwi, $team, $erf, $ist)
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(DISTINCT mit_id)');
		$query->from('#__wissensmatrix_mit_fwi AS zfwi');
		$query->join('LEFT', '#__wissensmatrix_mitarbeiter AS mit ON zfwi.mit_id = mit.id');
		$query->where('zfwi.fwi_id = ' . (int) $fwi);
		$query->where('mit.catid = ' . (int) $team);
		if ($ist)
		{
			$query->join('LEFT', '#__wissensmatrix_erfahrung AS level ON zfwi.ist = level.id');
		}
		else
		{
			$query->join('LEFT', '#__wissensmatrix_erfahrung AS level ON zfwi.soll = level.id');
		}
		$query->where('level.value >= ' . (int) $erf);
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Gets the levels
	 *
	 * @return    array    objects
	 * @since    3.0
	 */
	public function getLevels()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		// Join over Levels Category.
		$query->select('levels.id, levels.title, levels.value');
		$query->from('#__wissensmatrix_erfahrung AS levels');
		$query->join('LEFT', '#__categories AS c_levels ON c_levels.id = levels.catid');
		$query->where('c_levels.access IN (' . $groups . ')');
		$query->where('c_levels.published = 1');
		$query->order('levels.value ASC');

		$db->setQuery($query);

		return $db->loadObjectList('id');
	}

	/**
	 * Gets the diff
	 *
	 * @fwi        int        The id of the Fachwissen
	 * @team       int        The id of the team (category)
	 * @pot        bool    true for potential, false for manko
	 * @delta      int        The delta to search for
	 *
	 * @return    int        Count
	 * @since      1.0
	 */
	public function getDiff($fwi, $team, $pot = false, $delta = false)
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('COUNT(DISTINCT zfwi.mit_id)');
		$query->from('#__wissensmatrix_mit_fwi AS zfwi');
		$query->join('LEFT', '#__wissensmatrix_mitarbeiter AS mit ON mit.id = zfwi.mit_id');
		$query->join('LEFT', '#__wissensmatrix_erfahrung AS istlevel ON istlevel.id = zfwi.ist');
		$query->join('LEFT', '#__wissensmatrix_erfahrung AS solllevel ON solllevel.id = zfwi.soll');
		$query->where('mit.catid = ' . (int) $team);
		$query->where('zfwi.fwi_id = ' . (int) $fwi);
		if ($delta)
		{
			if ($pot)
			{
				$query->where('istlevel.value - solllevel.value = ' . (int) $delta);
			}
			else
			{
				$query->where('solllevel.value - istlevel.value = ' . (int) $delta);
			}
		}
		else
		{
			if ($pot)
			{
				$query->where('istlevel.value > solllevel.value');
			}
			else
			{
				$query->where('istlevel.value < solllevel.value');
			}
		}

		$db->setQuery($query);

		return $db->loadResult();
	}
}