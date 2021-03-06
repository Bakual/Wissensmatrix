<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Mitarbeiter controller class.
 *
 * @package        Wissensmatrix.Administrator
 */
class WissensmatrixControllerWorker extends JControllerForm
{
	/**
	 * Method override to check if you can add a new record.
	 * Quite useless now, but may change if we add ACLs to Wissensmatrix
	 *
	 * @param    array $data An array of input data.
	 *
	 * @return    boolean
	 */
	protected function allowAdd($data = array())
	{
		// Initialise variables.
		$user       = JFactory::getUser();
		$categoryId = JArrayHelper::getValue($data, 'catid', JFactory::getApplication()->input->get('filter_category_id', 0, 'int'), 'int');
		$allow      = null;

		if ($categoryId)
		{
			// If the category has been passed in the data or URL check it.
			$allow = $user->authorise('core.create', 'com_wissensmatrix.category.' . $categoryId);
		}

		if ($allow === null)
		{
			// In the absense of better information, revert to the component permissions.
			return parent::allowAdd();
		}
		else
		{
			return $allow;
		}
	}

	/**
	 * Method to check if you can add a new record.
	 * Quite useless now, but may change if we add ACLs to Wissensmatrix
	 *
	 * @param    array  $data An array of input data.
	 * @param    string $key  The name of the key for the primary key.
	 *
	 * @return    boolean
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Initialise variables.
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user     = JFactory::getUser();
		$userId   = $user->get('id');

		// Check general edit permission first.
		if ($user->authorise('core.edit', 'com_wissensmatrix.fwi.' . $recordId))
		{
			return true;
		}

		// Fallback on edit.own.
		// First test if the permission is available.
		if ($user->authorise('core.edit.own', 'com_wissensmatrix.fwi.' . $recordId))
		{
			// Now test the owner is the user.
			$ownerId = (int) isset($data['created_by']) ? $data['created_by'] : 0;
			if (empty($ownerId) && $recordId)
			{
				// Need to do a lookup from the model.
				$record = $this->getModel()->getItem($recordId);

				if (empty($record))
				{
					return false;
				}

				$ownerId = $record->created_by;
			}

			// If the owner matches 'me' then do the test.
			if ($ownerId == $userId)
			{
				return true;
			}
		}

		// Since there is no asset tracking, revert to the component permissions.
		return parent::allowEdit($data, $key);
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   object $model The model.
	 *
	 * @return  boolean     True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.7
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Worker', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_wissensmatrix&view=workers' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

	protected function getRedirectToItemAppend($recordId = null, $urlVar = null)
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);
		$modal  = JFactory::getApplication()->input->get('modal', 0, 'int');
		$return = $this->getReturnPage();

		if ($modal)
		{
			$append .= '&tmpl=component';
		}

		if ($return)
		{
			$append .= '&return=' . base64_encode($return);
		}

		return $append;
	}

	/**
	 * Get the return URL.
	 *
	 * If a "return" variable has been passed in the request
	 *
	 * @return    string    The return URL.
	 * @since    1.6
	 */
	protected function getReturnPage()
	{
		$return = JFactory::getApplication()->input->get('return', '', 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return)))
		{
			return false;
		}
		else
		{
			return base64_decode($return);
		}
	}

	/**
	 * Method to save a record.
	 *
	 * @param    string $key    The name of the primary key of the URL variable.
	 * @param    string $urlVar The name of the URL variable if different from the primary key (sometimes required to
	 *                          avoid router collisions).
	 *
	 * @return    Boolean    True if successful, false otherwise.
	 * @since    1.6
	 */
	public function save($key = null, $urlVar = 'id')
	{
		$result = parent::save($key, $urlVar);

		// If ok, redirect to the return page.
		if ($result && ($return = $this->getReturnPage()))
		{
			$this->setRedirect($return);
		}

		return $result;
	}
}