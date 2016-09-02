<?php
defined('_JEXEC') or die;

/**
 * @package     Joomla.Site
 * @subpackage  com_wissensmatrix
 */
class WissensmatrixControllerZfwig extends JControllerForm
{
	/**
	 * @since    1.6
	 */
	protected $view_item = 'zfwigform';

	/**
	 * @since    1.6
	 */
	protected $view_list = 'worker';

	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @see     JControllerLegacy
	 * @since   12.2
	 * @throws  Exception
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('reload', 'add');
	}


	/**
	 * Method to add a new record.
	 *
	 * @return    boolean    True if the worker can be added, false if not.
	 * @since    1.6
	 */
	public function add()
	{
		if (!parent::add())
		{
			// Redirect to the return page.
			$this->setRedirect($this->getReturnPage());
		}

		if ($this->task == 'reload')
		{
			$context   = "$this->option.edit.$this->context";
			$form_data = $this->input->get('jform', '', 'array');
			JFactory::getApplication()->setUserState($context . '.data', $form_data);
		}
	}

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param    array    An array of input data.
	 *
	 * @return    boolean
	 * @since    1.6
	 */
	protected function allowAdd($data = array())
	{
		$user       = JFactory::getUser();
		$categoryId = JArrayHelper::getValue($data, 'worker_catid', $this->input->getInt('catid'), 'int');
		$allow      = null;

		if ($this->task == 'reload')
		{
			$form_data  = $this->input->get('jform', '', 'array');
			$categoryId = JArrayHelper::getValue($form_data, 'worker_catid', 0, 'int');
		}

		if ($categoryId)
		{
			// If the category has been passed in the data or URL check it.
			return $user->authorise('wissensmatrix.edit.worker', 'com_wissensmatrix.category.' . $categoryId);
		}
		else
		{
			// In the absense of better information, revert to the component permissions.
			return parent::allowAdd();
		}
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param    array  $data An array of input data.
	 * @param    string $key  The name of the key for the primary key.
	 *
	 * @return    boolean
	 * @since    1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId   = (int) isset($data[$key]) ? $data[$key] : 0;
		$categoryId = 0;
		$allow      = null;

		if ($recordId)
		{
			// Need to do a lookup from the model.
			$record     = $this->getModel()->getItem($recordId);
			$categoryId = (int) $record->worker_catid;
		}

		if ($categoryId)
		{
			$user = JFactory::getUser();

			// The category has been set. Check the category permissions.
			if ($user->authorise('wissensmatrix.edit.worker', $this->option . '.category.' . $categoryId))
			{
				return true;
			}
		}
		else
		{
			// In the absense of better information, revert to the component permissions.
			return parent::allowEdit($data, $key);
		}
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param    string $key The name of the primary key of the URL variable.
	 *
	 * @return    Boolean    True if access level checks pass, false otherwise.
	 * @since    1.6
	 */
	public function cancel($key = 'a_id')
	{
		parent::cancel($key);

		// Redirect to the return page.
		$this->setRedirect($this->getReturnPage());
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param    string $key    The name of the primary key of the URL variable.
	 * @param    string $urlVar The name of the URL variable if different from the primary key (sometimes required to
	 *                          avoid router collisions).
	 *
	 * @return    Boolean    True if access level check and checkout passes, false otherwise.
	 * @since    1.6
	 */
	public function edit($key = null, $urlVar = 'a_id')
	{
		$result = parent::edit($key, $urlVar);

		return $result;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param    string $name   The model name. Optional.
	 * @param    string $prefix The class prefix. Optional.
	 * @param    array  $config Configuration array for model. Optional.
	 *
	 * @return    object    The model.
	 *
	 * @since    1.5
	 */
	public function getModel($name = 'zfwigform', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param    int    $recordId The primary key id for the item.
	 * @param    string $urlVar   The name of the URL variable for the id.
	 *
	 * @return    string    The arguments to append to the redirect URL.
	 * @since    1.6
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'a_id')
	{
		// Need to override the parent method completely.
		$layout = $this->input->get('layout', 'edit');
		$append = '';

		// Setup redirect info.

		// Assuming it's always in modal window, otherwise change here appropriate.
//		$tmpl   = $this->input->get('tmpl');
//		if ($tmpl) {
//			$append .= '&tmpl='.$tmpl;
//		}
		$append .= '&tmpl=component';

		$append .= '&layout=' . $layout;

		if ($recordId)
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}
		elseif ($this->task == 'reload')
		{
			$form_data = $this->input->get('jform', '', 'array');
			$recordId  = JArrayHelper::getValue($form_data, 'fwig_id', 0, 'int');
			$append .= '&' . $urlVar . '=' . $recordId;
			$append .= '&reload=true';
		}

		if ($itemId = $this->input->getInt('Itemid'))
		{
			$append .= '&Itemid=' . $itemId;
		}

		if ($mit_id = $this->input->getInt('mit_id', 0, 'get'))
		{
			$append .= '&mit_id=' . $mit_id;
		}
		elseif (isset($form_data))
		{
			$append .= '&mit_id=' . JArrayHelper::getValue($form_data, 'mit_id', 0, 'int');;
		}

		if ($catId = $this->input->getInt('catid', null, 'get'))
		{
			$append .= '&catid=' . $catId;
		}

		if ($return = $this->getReturnPage())
		{
			$append .= '&return=' . base64_encode($return);
		}

		return $append;
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   12.2
	 */
	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();

		$id = $this->input->get('mit_id', 0, 'int');

		if ($id)
		{
			$append .= '&id=' . $id;
		}

		$append .= '#fwis';

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
		$return = $this->input->get('return', null, 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return)))
		{
			return JURI::base();
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
	public function save($key = null, $urlVar = 'a_id')
	{
		// Load the backend helper for filtering.
		require_once JPATH_ADMINISTRATOR . '/components/com_wissensmatrix/helpers/wissensmatrix.php';

		$result = parent::save($key, $urlVar);

		// If ok, redirect to the return page.
		if ($result)
		{
			$this->setRedirect($this->getReturnPage());
		}

		return $result;
	}

	/**
	 * Removes an fwi from a worker.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function delete()
	{
		// Check for request forgeries
		JRequest::checkToken('request') or die(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid[] = JFactory::getApplication()->input->get('a_id', 0, 'int');

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Remove the item.
			if ($model->delete($cid))
			{
				$this->setMessage(JText::_($this->text_prefix . '_ITEM_DELETED'));
			}
			else
			{
				$this->setMessage($model->getError());
			}
		}

		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&id=' . $this->getRedirectToListAppend(), false));
	}
}