<?php
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Fachwissen model.
 *
 * @package        Wissensmatrix.Administrator
 */
class WissensmatrixModelFwig extends JModelAdmin
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
			if ($record->state != -2)
			{
				return;
			}
			$user = JFactory::getUser();

			if ($record->catid)
			{
				return $user->authorise('core.delete', 'com_wissensmatrix.category.' . (int) $record->catid);
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

		if (!empty($record->catid))
		{
			return $user->authorise('core.edit.state', 'com_wissensmatrix.category.' . (int) $record->catid);
		}
		else
		{
			return parent::canEditState($record);
		}
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
	public function getTable($type = 'Fwig', $prefix = 'WissensmatrixTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
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
		// Get the form.
		$form = $this->loadForm('com_wissensmatrix.fwig', 'fwig', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		// Determine correct permissions to check.
		if ((int) $this->getState('fwig.id'))
		{
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit');
			// Existing record. Can only edit own articles in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit.own');
		}
		else
		{
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.create');
		}

		// Modify the form based on Edit State access controls.
		if (!$this->canEditState((object) $data))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');
			$form->setFieldAttribute('podcast', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an article you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
			$form->setFieldAttribute('podcast', 'filter', 'unset');
		}

		return $form;
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
		$data = JFactory::getApplication()->getUserState('com_wissensmatrix.edit.fwig.data', array());

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

		$table->title_de = htmlspecialchars_decode($table->title_de, ENT_QUOTES);
		$table->title_fr = htmlspecialchars_decode($table->title_fr, ENT_QUOTES);
		$table->title_it = htmlspecialchars_decode($table->title_it, ENT_QUOTES);
		$table->alias    = JApplication::stringURLSafe($table->alias);

		if (empty($table->alias))
		{
			$table->alias = JApplication::stringURLSafe($table->title_de);
			if (empty($table->alias))
			{
				$table->alias = JFactory::getDate()->format("Y-m-d-H-i-s");
			}
		}

		if (!empty($table->metakey))
		{
			// only process if not empty
			$bad_characters = array("\n", "\r", '"', '<', '>'); // array of characters to remove
			$after_clean    = JString::str_ireplace($bad_characters, '', $table->metakey); // remove bad characters
			$keys           = explode(',', $after_clean); // create array using commas as delimiter
			$clean_keys     = array();
			foreach ($keys as $key)
			{
				if (trim($key))
				{  // ignore blank keywords
					$clean_keys[] = trim($key);
				}
			}
			$table->metakey = implode(', ', $clean_keys); // put array back together delimited by ", "
		}

		// Reorder the articles within the category so the new fwig is first
		if (empty($table->id))
		{
			$table->reorder('catid = ' . (int) $table->catid . ' AND state >= 0');
		}
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param    object    A record object.
	 *
	 * @return    array    An array of conditions to add to add to ordering queries.
	 * @since    1.6
	 */
	protected function getReorderConditions($table = null)
	{
		$condition   = array();
		$condition[] = 'catid = ' . (int) $table->catid;

		return $condition;
	}

	/**
	 * Changing the state of bool. Copy of the parent function publish
	 */
	function bool(&$pks, $value = 1)
	{
		// Initialise variables.
		$dispatcher = JDispatcher::getInstance();
		$user       = JFactory::getUser();
		$table      = $this->getTable();
		$pks        = (array) $pks;

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			$table->reset();

			if ($table->load($pk))
			{
				if (!$this->canEditState($table))
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDIT_STATE_NOT_PERMITTED'));
				}
			}
		}

		// Attempt to change the state of the records.
		if (!$table->bool($pks, $value, $user->get('id')))
		{
			$this->setError($table->getError());

			return false;
		}

		$context = $this->option . '.' . $this->name;

		return true;
	}

	/**
	 * Method to perform batch operations on an item or a set of items.
	 * Copy from modeladmin with added commands for speaker and series.
	 *
	 * @param   array $commands An array of commands to perform.
	 * @param   array $pks      An array of item ids.
	 * @param   array $contexts An array of item contexts.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   11.1
	 */
	public function batch($commands, $pks, $contexts)
	{
		// Sanitize user ids.
		$pks = array_unique($pks);
		JArrayHelper::toInteger($pks);

		// Remove any values of zero.
		if (array_search(0, $pks, true))
		{
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks))
		{
			$this->setError(JText::_('JGLOBAL_NO_ITEM_SELECTED'));

			return false;
		}

		$done = false;

		if (!empty($commands['category_id']))
		{
			$cmd = JArrayHelper::getValue($commands, 'move_copy', 'c');

			if ($cmd == 'c')
			{
				$result = $this->batchCopy($commands['category_id'], $pks, $contexts);
				if (is_array($result))
				{
					$pks = $result;
				}
				else
				{
					return false;
				}
			}
			elseif ($cmd == 'm' && !$this->batchMove($commands['category_id'], $pks, $contexts))
			{
				return false;
			}
			$done = true;
		}

		if (!empty($commands['language_id']))
		{
			if (!$this->batchLanguage($commands['language_id'], $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}

		if (!$done)
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch copy items to a new category or current.
	 * Override from modeladmin to adjust title field.
	 *
	 * @param   integer $value    The new category.
	 * @param   array   $pks      An array of row IDs.
	 * @param   array   $contexts An array of item contexts.
	 *
	 * @return  mixed  An array of new IDs on success, boolean false on failure.
	 *
	 * @since    11.1
	 */
	protected function batchCopy($value, $pks, $contexts)
	{
		$categoryId = (int) $value;

		$table = $this->getTable();
		$i     = 0;

		// Check that the category exists
		if ($categoryId)
		{
			$categoryTable = JTable::getInstance('Category');
			if (!$categoryTable->load($categoryId))
			{
				if ($error = $categoryTable->getError())
				{
					// Fatal error
					$this->setError($error);

					return false;
				}
				else
				{
					$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));

					return false;
				}
			}
		}

		if (empty($categoryId))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));

			return false;
		}

		// Check that the user has create permission for the component
		$extension = JFactory::getApplication()->input->get('option', '');
		$user      = JFactory::getUser();
		if (!$user->authorise('core.create', $extension . '.category.' . $categoryId))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));

			return false;
		}

		// Parent exists so we let's proceed
		while (!empty($pks))
		{
			// Pop the first ID off the stack
			$pk = array_shift($pks);

			$table->reset();

			// Check that the row actually exists
			if (!$table->load($pk))
			{
				if ($error = $table->getError())
				{
					// Fatal error
					$this->setError($error);

					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Alter the title & alias
			// Custom: defining the fwig_title
			$data            = $this->generateNewTitle($categoryId, $table->alias, $table->title_de);
			$table->title_de = $data['0'];
			$table->alias    = $data['1'];

			// Reset the ID because we are making a copy
			$table->id = 0;

			// New category ID
			$table->catid = $categoryId;

			// TODO: Deal with ordering?
			//$table->ordering	= 1;

			// Check the row.
			if (!$table->check())
			{
				$this->setError($table->getError());

				return false;
			}

			// Store the row.
			if (!$table->store())
			{
				$this->setError($table->getError());

				return false;
			}

			// Get the new item ID
			$newId = $table->get('id');

			// Add the new ID to the array
			$newIds[$i] = $newId;
			$i++;
		}

		// Clean the cache
		$this->cleanCache();

		return $newIds;
	}

	/**
	 * Batch speaker changes for a group of rows.
	 *
	 * @param   string $value    The new value matching a speaker.
	 * @param   array  $pks      An array of row IDs.
	 * @param   array  $contexts An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   11.3
	 */
	protected function batchSpeaker($value, $pks, $contexts)
	{
		// Set the variables
		$user  = JFactory::getUser();
		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($user->authorise('core.edit', $contexts[$pk]))
			{
				$table->reset();
				$table->load($pk);
				$table->speaker_id = $value;

				if (!$table->store())
				{
					$this->setError($table->getError());

					return false;
				}
			}
			else
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch serie changes for a group of rows.
	 *
	 * @param   string $value    The new value matching a serie.
	 * @param   array  $pks      An array of row IDs.
	 * @param   array  $contexts An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   11.3
	 */
	protected function batchSerie($value, $pks, $contexts)
	{
		// Set the variables
		$user  = JFactory::getUser();
		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($user->authorise('core.edit', $contexts[$pk]))
			{
				$table->reset();
				$table->load($pk);
				$table->series_id = $value;

				if (!$table->store())
				{
					$this->setError($table->getError());

					return false;
				}
			}
			else
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}
}