<?php
defined('_JEXEC') or die;

/**
 * Wissensmatrix model.
 *
 */
class WissensmatrixModelZwbiform extends JModelAdmin
{
	/**
	 * @var        string    The prefix to use with controller messages.
	 */
	protected $text_prefix = 'COM_WISSENSMATRIX';

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param    object    A record object.
	 *
	 * @return    boolean    True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since    1.6
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id))
		{
			$user = JFactory::getUser();

			if (!$record->mit_id)
			{
				return;
			}

			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('catid');
			$query->from('#__wissensmatrix_mitarbeiter');
			$query->where('id = ' . $record->mit_id);
			$db->setQuery($query);
			$catid = $db->loadResult();

			if ($catid)
			{
				return $user->authorise('core.edit.worker', 'com_wissensmatrix.category.' . (int) $catid);
			}
			else
			{
				return parent::canDelete($record);
			}
		}
	}

	/**
	 * Method to test whether a records state can be changed.
	 *
	 * @param    object    A record object.
	 *
	 * @return    boolean    True if allowed to change the state of the record. Defaults to the permission set in the
	 *                       component.
	 * @since    1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check against the category.
		if (!empty($record->worker_catid))
		{
			return $user->authorise('core.edit.worker', 'com_wissensmatrix.category.' . (int) $record->worker_catid);
		}
		// Default to component settings if neither article nor category known.
		else
		{
			return parent::canEditState($record);
		}
	}

	/**
	 * Method to get the record form.
	 *
	 * @param    array   $data     An optional array of data for the form to interogate.
	 * @param    boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_wissensmatrix.zwbi', 'zwbi', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		// Determine correct permissions to check.
		if ($this->getState('zwbi.id'))
		{
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit.worker');
		}
		else
		{
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit.worker');
		}

		if (!isset($data['worker_catid']))
		{
			$data['worker_catid'] = $form->getValue('worker_catid');
		}

		// Modify the form based on Edit State access controls.
		if (!$this->canEditState((object) $data))
		{
			// Disable fields for display.
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an article you can edit.
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		// If mit_id is set set the worker as attribute to the wbilist formfield
		if (!isset($data['mit_id']))
		{
			$data['mit_id'] = $form->getValue('mit_id');
		}
		if ($data['mit_id'])
		{
			$form->setFieldAttribute('wbi_id', 'mit_id', $data['mit_id']);
		}

		return $form;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param    type      The table type to instantiate
	 * @param    string    A prefix for the table class name. Optional.
	 * @param    array     Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 * @since    1.6
	 */
	public function getTable($type = 'Zwbi', $prefix = 'WissensmatrixTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getItem($pk = null)
	{
		$item  = parent::getItem($pk);
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		if (!$item->mit_id)
		{
			$item->mit_id = JFactory::getApplication()->input->get('mit_id', 0, 'INT');
		}

		$query->select('catid as worker_catid');
		$query->select('CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(\':\', id, alias) ELSE id END as worker_slug');
		$query->from('#__wissensmatrix_mitarbeiter');
		$query->where('id = ' . $item->mit_id);
		$db->setQuery($query);
		$row = $db->loadAssoc();

		foreach ($row as $key => $value)
		{
			$item->$key = $value;
		}

		return $item;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_wissensmatrix.edit.zwbi.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since    1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		$table->bemerkung = htmlspecialchars_decode($table->bemerkung, ENT_QUOTES);
	}

	/**
	 * Get the return URL.
	 *
	 * @return    string    The return URL.
	 * @since    1.6
	 */
	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since    1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('a_id');
		$this->setState('zwbi.id', $pk);
		// Add compatibility variable for default naming conventions.
		$this->setState('zwbiform.id', $pk);

		$return = $app->input->get('return', null, 'base64');

		if (!JUri::isInternal(base64_decode($return)))
		{
			$return = null;
		}

		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('layout', $app->input->get('layout'));
	}
}
