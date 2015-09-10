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
class WissensmatrixModelWbig extends JModelItem
{
	public function populateState($ordering = null, $direction = null)
	{
		$app    = JFactory::getApplication();
		$params = $app->getParams();

		// Load the object state.
		$id = $app->input->get('id', 0, 'int');
		$this->setState('wbig.id', $id);

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
		$id = (!empty($id)) ? $id : (int) $this->getState('wbig.id');

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
						'wbig.id, wbig.catid, ' .
						'wbig.checked_out, wbig.checked_out_time, wbig.language, ' .
						'wbig.hits, wbig.state, wbig.created, wbig.created_by, ' .
						'CASE WHEN CHAR_LENGTH(wbig.alias) THEN CONCAT_WS(\':\', wbig.id, wbig.alias) ELSE wbig.id END as slug'
					)
				);
				$query->from('#__wissensmatrix_weiterbildunggruppe AS wbig');

				// Create title from active language
				$lang = substr(JFactory::getLanguage()->getTag(), 0, 2);
				$query->select('wbig.`title_' . $lang . '` AS title');

				// Join on category table (for team).
				$query->select('c.title AS category_title, c.access AS category_access');
				$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as catslug');
				$query->join('LEFT', '#__categories AS c on c.id = wbig.catid');
				$query->where('c.published = 1');

				$query->where('wbig.id = ' . (int) $id);
				$query->where('wbig.state = 1');

				// Join over users for the author names.
				$query->select("user.name AS author");
				$query->join('LEFT', '#__users AS user ON user.id = wbig.created_by');

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
	 * Method to increment the hit counter for the wbigs
	 *
	 * @param    int        Optional ID of the wbigs.
	 *
	 * @return    boolean    True on success
	 * @since    1.5
	 */
	public function hit($id = null)
	{
		if (empty($id))
		{
			$id = $this->getState('wbig.id');
		}

		$wbig = $this->getTable('wbig', 'WissensmatrixTable');

		return $wbig->hit($id);
	}
}