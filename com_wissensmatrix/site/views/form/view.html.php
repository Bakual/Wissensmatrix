<?php
defined('_JEXEC') or die;

/**
 * HTML Article View class for the Wissensmatrix component
 *
 */
class WissensmatrixViewForm extends JViewLegacy
{
	protected $form;

	protected $item;

	protected $return_page;

	protected $state;

	public function display($tpl = null)
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		// Get model data.
		$this->state       = $this->get('State');
		$this->item        = $this->get('Item');
		$this->form        = $this->get('Form');
		$this->return_page = $this->get('ReturnPage');

		if (empty($this->item->id))
		{
			$authorised = ($user->authorise('core.create', 'com_wissensmatrix') || (count($user->getAuthorisedCategories('com_wissensmatrix', 'core.create'))));
		}
		else
		{
			$authorised = ($user->authorise('wissensmatrix.edit.worker', 'com_wissensmatrix') || $user->authorise('wissensmatrix.edit.worker', 'com_wissensmatrix.category.' . $this->item->catid));
		}

		if ($authorised !== true)
		{
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		if (!empty($this->item))
		{
			$this->form->bind($this->item);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));

			return false;
		}

		// Create a shortcut to the parameters.
		$params = &$this->state->params;

		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->params = $params;
		$this->user   = $user;

		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_WISSENSMATRIX_FORM_EDIT_WORKER'));
		}

		$title = $this->params->def('page_title', JText::_('COM_WISSENSMATRIX_FORM_EDIT_WORKER'));
		if ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		$pathway = $app->getPathWay();
		$pathway->addItem($title, '');

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
