<?php
/**
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');

/**
 * @package		Wissensmatrix
 */
// Based on com_contact
class WissensmatrixModelWorker extends JModelItem
{
	public function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
		$params	= $app->getParams();

		// Load the object state.
		$id	= $app->input->get('id', 0, 'int');
		$this->setState('worker.id', $id);

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to get an ojbect.
	 *
	 * @param	integer	The id of the object to get.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function &getItem($id = null)
	{
		// Initialise variables.
		$id = (!empty($id)) ? $id : (int) $this->getState('worker.id');

		if ($this->_item === null) {
			$this->_item = array();
		}

		if (!isset($this->_item[$id])) {

			try {
				$db = $this->getDbo();
				$query = $db->getQuery(true);

				$query->select(
					$this->getState(
						'item.select',
						'worker.id, worker.uid, worker.vorname, worker.name, worker.catid, worker.geb, worker.eintritt, '.
						'worker.checked_out, worker.checked_out_time, worker.language, '.
						'worker.hits, worker.state, worker.created, worker.created_by, '.
						'CONCAT(worker.vorname, \' \', worker.name) as title, '.
						'CASE WHEN CHAR_LENGTH(worker.alias) THEN CONCAT_WS(\':\', worker.id, worker.alias) ELSE worker.id END as slug'
					)
				);
				$query->from('#__wissensmatrix_mitarbeiter AS worker');

				// Join on category table (for team).
				$query->select('c.title AS category_title, c.access AS category_access');
				$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as catslug');
				$query->join('LEFT', '#__categories AS c on c.id = worker.catid');
				$query->where('(worker.catid = 0 OR c.published = 1)');

				$query->where('worker.id = '.(int)$id);
				$query->where('worker.state = 1');

				// Join on mitarbeiter table for template.
				$query->select('CONCAT(t.vorname, \' \', t.name) AS template_title');
				$query->join('LEFT', '#__wissensmatrix_mitarbeiter AS t on t.id = worker.template_id');

				// Join over users for the author names.
				$query->select("user.name AS author");
				$query->join('LEFT', '#__users AS user ON user.id = worker.created_by');

				$db->setQuery($query);

				$data = $db->loadObject();

				if ($error = $db->getErrorMsg()) {
					throw new Exception($error);
				}

				if (empty($data)) {
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
	 * Method to increment the hit counter for the workers
	 *
	 * @param	int		Optional ID of the workers.
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function hit($id = null)
	{
		if (empty($id)) {
			$id = $this->getState('worker.id');
		}

		$worker = $this->getTable('Worker', 'WissensmatrixTable');
		return $worker->hit($id);
	}
}