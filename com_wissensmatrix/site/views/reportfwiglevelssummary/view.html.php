<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * HTML View class for the Wissensmatrix Component
 */
class WissensmatrixViewReportfwiglevelssummary extends JViewLegacy
{
	function display($tpl = null)
	{
		// Get some data from the model
		$this->model = $this->getModel();
		$this->state = $this->get('State');
		$this->state->set('list.start', 0);
		$this->state->set('list.limit', 0);
		$this->items = $this->get('Items');

		$this->params = $this->state->get('params');

		$this->fwis_model = $this->getModel('fwis');
		$this->fwis_state = $this->fwis_model->getState();
		$this->fwis_state->set('list.start', 0);
		$this->fwis_state->set('list.limit', 0);
		$this->levels = $this->fwis_model->getLevels();
		foreach ($this->levels as $key => $level)
		{
			$levels[] = $key;
		}
		$levels = implode(',', $levels);

		foreach ($this->levels as $key => $level)
		{
			if (!$level->value) continue;
			$this->ist_total[$key]  = $this->model->getLevelSummary($level->value, $levels, false, false);
			$this->soll_total[$key] = $this->model->getLevelSummary($level->value, $levels, true, false);
			$this->ist[$key]        = $this->model->getLevelSummary($level->value, $levels, false, true);
			$this->soll[$key]       = $this->model->getLevelSummary($level->value, $levels, true, true);
		}

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
			$this->params->def('page_heading', JText::_('COM_WISSENSMATRIX_REPORTFWIGLEVELS_TITLE'));
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