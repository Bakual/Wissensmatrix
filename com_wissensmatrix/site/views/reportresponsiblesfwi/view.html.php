<?php
defined('_JEXEC') or die;

/**
 * HTML View class for the Wissensmatrix Component
 */
class WissensmatrixViewReportresponsiblesfwi extends JViewLegacy
{
	function display($tpl = null)
	{
		$app = JFactory::getApplication();

		// Get the model
		$this->model = $this->getModel();

		// Get some data from the model
		$this->state = $this->get('State');
		$this->state->set('list.start', 0);
		$this->state->set('list.limit', 0);
		$this->state->set('filter.responsible', true);

		$this->fwi_id = $app->input->get('id', 0, 'INT');

		if ($this->fwi_id)
		{
			$this->state->set('filter.fwi_id', $this->fwi_id);
		}

		$this->items  = $this->get('Items');
		$this->parent = $this->get('Parent');

		// Get Workers for selected teams
/*		$this->workermodel = $this->getModel('Workers');
		$this->w_state     = $this->workermodel->getState();
		$this->w_state->set('list.start', 0);
		$this->w_state->set('list.limit', 0);
		$this->w_state->set('filter.responsible', 1);
		$this->w_state->set('filter.fwi_id', $this->item->id);
		$this->workers = $this->workermodel->getItems();
		$this->parent  = $this->workermodel->getParent(); */

		$this->params = $this->state->get('params');

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
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_WISSENSMATRIX_REPORTRESPONSIBLESFWI_TITLE'));
		}
		$title = $this->params->get('page_title', '');
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
		$this->document->setTitle($title);

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