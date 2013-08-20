<?php
defined('_JEXEC') or die;
jimport( 'joomla.application.component.view');
/**
 * HTML View class for the Wissensmatrix Component
 */
class WissensmatrixViewReportfwiteam extends JViewLegacy
{
	function display($tpl = null)
	{
		// Get the model
		$this->model		= $this->getModel();

		// Get some data from the model
		$this->state		= $this->get('State');
		$this->item			= $this->get('Item');

		// Get Workers for selected teams
		$this->workermodel = $this->getModel('Workers');
		$this->workermodel->getState();
		$this->workermodel->setState('list.start', 0);
		$this->workermodel->setState('list.limit', 0);
		$this->workers		= $this->workermodel->getItems();
		$this->w_state		= $this->workermodel->getState();
		$this->parent		= $this->workermodel->getParent();

		$this->params		= $this->state->get('params');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$js = 'function clear_all(){
			if(document.id(\'filter_teamid\')){
				document.id(\'filter_teamid\').value=0;
			}
			if(document.id(\'filter-search\')){
				document.id(\'filter-search\').value="";
			}
		}';
		$this->document->addScriptDeclaration($js);

		$this->pageclass_sfx	= htmlspecialchars($this->params->get('pageclass_sfx'));
		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app	= JFactory::getApplication();
		$menus	= $app->getMenu();

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_WISSENSMATRIX_REPORTFWIGTEAM_TITLE'));
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