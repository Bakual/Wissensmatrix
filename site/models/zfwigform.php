<?php
// No direct access.
defined('_JEXEC') or die;

/**
 * Fachwissen Zwischentabelle model.
 *
 */
class WissensmatrixModelZfwigform extends JModelForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 */
	protected $text_prefix = 'COM_WISSENSMATRIX';

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	A record object.
	 * @return	boolean	True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id)) {
			$user	= JFactory::getUser();

			if (!$record->mit_id)
			{
				return;
			}

			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);

			$query->select('catid');
			$query->from('#__wissensmatrix_mitarbeiter');
			$query->where('id = '.$record->mit_id);
			$db->setQuery($query);
			$catid	= $db->loadResult();

			if ($catid) {
				// Use edit rights for this
				return $user->authorise('core.worker.edit', 'com_wissensmatrix.category.'.(int) $catid);
			}
			else {
				return parent::canDelete($record);
			}
		}
	}

	/**
	 * Method to test whether a records state can be changed.
	 *
	 * @param	object	A record object.
	 * @return	boolean	True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check against the category.
		if (!empty($record->worker_catid)) {
			return $user->authorise('core.edit.state', 'com_wissensmatrix.category.'.(int) $record->worker_catid);
		}
		// Default to component settings if neither article nor category known.
		else {
			return $user->authorise('core.edit.state', 'com_wissensmatrix');
		}
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Zfwi', $prefix = 'WissensmatrixTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_wissensmatrix.zfwig', 'zfwig', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		// Determine correct permissions to check.
		if ($this->getState('zfwig.id') && !$app->input->get('reload', 0, 'bool')) {
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit.worker');
			$form->setFieldAttribute('fwig_id', 'readonly', 'true');
		} else {
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit.worker');

			// If mit_id is set set the worker as attribute to the fwilist formfield
			if (!isset($data['mit_id']))
			{
				$data['mit_id'] = $form->getValue('mit_id');
			}
			if ($data['mit_id'])
			{
				$form->setFieldAttribute('fwig_id', 'mit_id', $data['mit_id']);
			}
		}

		if (!isset($data['worker_catid']))
		{
			$data['worker_catid'] = $form->getValue('worker_catid');
		}

		// Modify the form based on Edit State access controls.
		if (!$this->canEditState((object) $data)) {
			// Disable fields for display.
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an article you can edit.
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItem($pk = null)
	{
		$app	= JFactory::getApplication();
		$pk		= (!empty($pk)) ? $pk : (int) $this->getState('zfwig.id');
		$mit_id	= $app->input->get('mit_id', 0, 'INT');

		// Get from Form
		if (!$mit_id)
		{
			$form	= $app->input->get('jform', array(), 'array');
			$mit_id	= (int)$form['mit_id'];
		}

		// Get some information about worker
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('id as mit_id, catid as worker_catid, template_id as worker_template_id');
		$query->select('CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(\':\', id, alias) ELSE id END as worker_slug');
		$query->from('#__wissensmatrix_mitarbeiter');
		$query->where('id = '.$mit_id);
		$db->setQuery($query);
		$item	= $db->loadObject();

		// Load FWIG id into object
		$item->fwig_id	= $pk;

		return $item;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_wissensmatrix.edit.zfwig.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');
	}

	/**
	 * Get the return URL.
	 *
	 * @return	string	The return URL.
	 * @since	1.6
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
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('a_id');

		$this->setState('zfwig.id', $pk);
		// Add compatibility variable for default naming conventions.
		$this->setState('zfwigform.id', $pk);

		$return = $app->input->get('return', null, 'base64');

		if (!JUri::isInternal(base64_decode($return))) {
			$return = null;
		}

		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params	= $app->getParams();
		$this->setState('params', $params);

		$this->setState('layout', $app->input->get('layout'));
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 */
	public function save($data)
	{
		$db		= JFactory::getDbo();
		$mit_id	= (int)$data['mit_id'];
		$columns = array('mit_id', 'fwi_id', 'ist', 'soll');
		require_once(JPATH_COMPONENT.'/models/fwis.php');
		$fwi_model	= new WissensmatrixModelFwis;
		$levels	= $fwi_model->getLevels();

		foreach ($data['fwis'] as $fwi => $values)
		{
			$query	= $db->getQuery(true);
			$query->delete();
			$query->from('#__wissensmatrix_mit_fwi');
			$query->where('mit_id = '.$mit_id);
			$query->where('fwi_id = '.(int)$fwi);
			$db->setQuery($query);
			$result = $db->execute();

			if ($levels[$values['ist']]->value || $levels[$values['soll']]->value)
			{
				$query	= $db->getQuery(true);
				$query->insert('#__wissensmatrix_mit_fwi');
				$query->columns($db->quoteName($columns));
				$query->values($mit_id.','.(int)$fwi.','.(int)$values['ist'].','.(int)$values['soll']);
				$db->setQuery($query);
				$result = $db->execute();
			}
		}

		return true;
	}
}