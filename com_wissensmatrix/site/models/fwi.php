<?php
/**
 * @copyright      Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');

/**
 * @package        Wissensmatrix
 */
class WissensmatrixModelFwi extends JModelItem
{
	public function populateState($ordering = null, $direction = null)
	{
		$app    = JFactory::getApplication();
		$params = $app->getParams();

		// Load the object state.
		$id = $app->input->get('id', 0, 'int');
		$this->setState('fwi.id', $id);

		// Category filter (priority on request so subcategories work)
		// Team in this case, not used but selection has to be saved in userstate
		$teamid = $app->getUserStateFromRequest('com_wissensmatrix.team.id', 'teamid', null, 'int');

		// Check default team
		if ($teamid === null)
		{
			$teamid = WissensmatrixHelperWissensmatrix::getDefaultTeam();
			JFactory::getApplication()->setUserState('com_wissensmatrix.team.id', $teamid);
		}

		$this->setState('team.id', $teamid);

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to get an ojbect.
	 *
	 * @param    integer    The id of the object to get.
	 *
	 * @return    mixed    Object on success, false on failure.
	 */
	public function &getItem($id = null)
	{
		// Initialise variables.
		$id = (!empty($id)) ? $id : (int) $this->getState('fwi.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$id]))
		{

			try
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true);

				$query->select(
					$this->getState(
						'item.select',
						'fwi.id, fwi.catid, ' .
						'fwi.checked_out, fwi.checked_out_time, fwi.language, ' .
						'fwi.hits, fwi.state, fwi.created, fwi.created_by, ' .
						'CASE WHEN CHAR_LENGTH(fwi.alias) THEN CONCAT_WS(\':\', fwi.id, fwi.alias) ELSE fwi.id END as slug'
					)
				);
				$query->from('#__wissensmatrix_fachwissen AS fwi');

				// Create title from active language
				$lang = substr(JFactory::getLanguage()->getTag(), 0, 2);
				$query->select('fwi.`title_' . $lang . '` AS title');

				// Join on category table (for team).
				$query->select('c.title AS category_title, c.access AS category_access');
				$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as catslug');
				$query->join('LEFT', '#__categories AS c on c.id = fwi.catid');
				$query->where('c.published = 1');

				$query->where('fwi.id = ' . (int) $id);
				$query->where('fwi.state = 1');

				// Join over users for the author names.
				$query->select("user.name AS author");
				$query->join('LEFT', '#__users AS user ON user.id = fwi.created_by');

				// Join over fwig.
				$query->select('fwig.`id` as fwig_id, fwig.`title_' . $lang . '` AS fwig_title');
				$query->select('CASE WHEN CHAR_LENGTH(fwig.alias) THEN CONCAT_WS(\':\', fwig.id, fwig.alias) ELSE fwig.id END as fwig_slug');
				$query->join('LEFT', '#__wissensmatrix_fachwissengruppe AS fwig ON fwig.id = fwi.fwig_id');

				$db->setQuery($query);

				$data = $db->loadObject();

				if ($error = $db->getErrorMsg())
				{
					throw new Exception($error);
				}

				if (empty($data))
				{
					throw new JException(JText::_('JGLOBAL_RESOURCE_NOT_FOUND'), 404);
				}

				$this->_item[$id] = $data;
			}
			catch (JException $e)
			{
				$this->setError($e);
				$this->_item[$id] = false;
			}
		}

		return $this->_item[$id];
	}

	/**
	 * Method to increment the hit counter for the fwis
	 *
	 * @param    int        Optional ID of the fwis.
	 *
	 * @return    boolean    True on success
	 * @since    1.5
	 */
	public function hit($id = null)
	{
		if (empty($id))
		{
			$id = $this->getState('fwi.id');
		}

		$fwi = $this->getTable('fwi', 'WissensmatrixTable');

		return $fwi->hit($id);
	}

	/**
	 * Get the ist and soll value for a given Fachwissen and Worker. (Copy from fwis)
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
		$query->select('soll_level.title as template');
		$query->from('`#__wissensmatrix_mit_fwi` as zfwis');
		$query->join('LEFT', '#__wissensmatrix_erfahrung AS soll_level ON zfwis.soll = soll_level.id');
		$query->where('fwi_id = ' . (int) $fwi);
		$query->where('mit_id = ' . (int) $template_id);
		$db->setQuery($query);
		$item['template'] = $db->loadResult();

		return $item;
	}
}