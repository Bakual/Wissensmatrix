<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * HTML View class for the Wissensmatrix Component
 */
class WissensmatrixViewReportswbis extends JViewLegacy
{
	function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->state->set('wbig.id', JFactory::getApplication()->input->get('id', 0, 'int'));
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->pagination->setAdditionalUrlParam('teamid', $this->state->get('team.id'));
		$this->pagination->setAdditionalUrlParam('id', $this->state->get('wbig.id'));
		$this->parent = JCategories::getInstance('Wissensmatrix')->get($this->state->get('team.id', 'root'))->getParent();

		// Get Wbigs for dropdown and add "- select wbig -"
		$wbigsmodel = $this->getModel('Wbigs');
		$wbig_state = $wbigsmodel->getState();
		$wbig_state->set('filter.search', '');
		$wbig_state->set('list.limit', 0);
		$wbig_state->set('list.start', 0);
		$wbig_state->set('list.ordering', 'title');
		$wbig_state->set('list.direction', 'asc');
		$this->wbigs = $wbigsmodel->getItems();

		$this->params = $this->state->get('params');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
		$this->maxLevel      = $this->params->get('maxLevel', -1);
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
			$this->params->def('page_heading', JText::_('COM_WISSENSMATRIX_REPORTSWBIGS_TITLE'));
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