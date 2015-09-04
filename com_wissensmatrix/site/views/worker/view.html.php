<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * HTML View class for the Wissensmatrix Component
 */
class WissensmatrixViewWorker extends JViewLegacy
{
	function display($tpl = null)
	{
		$app = JFactory::getApplication();

		if (!$app->input->get('id', 0, 'int'))
		{
			$app->redirect(JRoute::_('index.php?option=com_wissensmatrix&view=workers'), JText::_('JGLOBAL_RESOURCE_NOT_FOUND'), 'error');
		}

		// Initialise variables.
		$user = JFactory::getUser();

		// Get some data from the model
		$this->item = $this->get('Item');

		if (!$this->item)
		{
			$app->redirect(JRoute::_('index.php?option=com_wissensmatrix&view=workers'), JText::_('JGLOBAL_RESOURCE_NOT_FOUND'), 'error');
		}

		$this->print = $app->input->getBool('print');

		// check if access is not public

		if ($this->item->category_access)
		{
			$groups  = $user->getAuthorisedViewLevels();
			$canView = ($user->authorise('wissensmatrix.edit.worker', 'com_wissensmatrix') or $user->authorise('wissensmatrix.view.worker', 'com_wissensmatrix.category.' . $this->item->catid));

			if (!in_array($this->item->category_access, $groups) or !$canView)
			{
				$app->redirect(JRoute::_('index.php?option=com_wissensmatrix&view=workers'), JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			}
		}

		// Get Params
		$state        = $this->get('State');
		$this->params = $state->get('params');

		// Update Statistic
		if ($this->params->get('track_workers', 1))
		{
			if (!$user->authorise('com_wissensmatrix.hit', 'com_wissensmatrix'))
			{
				$model = $this->getModel();
				$model->hit();
			}
		}

		// Get wbis data from the wbis model
		$wbi_model       = $this->getModel('Wbis');
		$this->state_wbi = $wbi_model->getState();
		$this->state_wbi->set('worker.id', $state->get('worker.id'));
		$this->state_wbi->set('list.start', 0);
		$this->state_wbi->set('list.limit', 0);
		$this->state_wbi->set('filter.search', '');
		$this->wbis = $wbi_model->getItems();

		// Get fwis data from the fwis model
		$fwi_model       = $this->getModel('Fwis');
		$this->state_fwi = $fwi_model->getState();
		$this->state_fwi->set('worker.id', $state->get('worker.id'));
		$this->state_fwi->set('list.start', 0);
		$this->state_fwi->set('list.limit', 0);
		$this->state_fwi->set('filter.search', '');
		$this->fwis = $fwi_model->getItems();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app     = JFactory::getApplication();
		$menus   = $app->getMenu();
		$pathway = $app->getPathway();

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_WISSENSMATRIX_WORKER_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		// if the menu item does not concern this article
		if ($menu && ($menu->query['option'] != 'com_wissensmatrix' || $menu->query['view'] != 'worker' || $menu->query['id'] != $this->item->id))
		{
			if ($this->item->title)
			{
				$title = $this->item->title;
			}
		}

		// Check for empty title and add site name if param is set
		if (empty($title))
		{
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		if (empty($title))
		{
			$title = $this->item->title;
		}
		$this->document->setTitle($title);

		// add Breadcrumbs
		$pathway = $app->getPathway();
		$pathway->addItem($this->item->title);
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'title'          => JText::_('JGLOBAL_TITLE'),
			'zwbi.date'      => JText::_('JDATE'),
			'zwbi.status_id' => JText::_('JSTATUS'),
		);
	}
}