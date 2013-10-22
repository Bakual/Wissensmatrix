<?php
defined('_JEXEC') or die;
jimport( 'joomla.application.component.view');
/**
 * HTML View class for the Wissensmatrix Component
 */
class WissensmatrixViewReportfwigdiff extends JViewLegacy
{
	function display($tpl = null)
	{
		// Get some data from the model
		$this->model		= $this->getModel();
		$this->state		= $this->get('State');
		$this->state->set('fwig.id', JFactory::getApplication()->input->get('id', 0, 'int'));
		$this->state->set('list.start', 0);
		$this->state->set('list.limit', 0);
		$this->items		= $this->get('Items');
		$this->levels		= $this->get('Levels');

		// Get Workers for selected teams
		$this->workermodel = $this->getModel('Workers');
		$this->w_state		= $this->workermodel->getState();
		$this->w_state->set('list.start', 0);
		$this->w_state->set('list.limit', 0);
		$this->workers		= $this->workermodel->getItems();
		$this->parent		= $this->workermodel->getParent();

		$this->params		= $this->state->get('params');

		// Get list of teams
		$this->teams		= array();
		$this->exclude		= $this->params->get('exclude_cat');
		$this->getTeams($this->workermodel->getCategory());
		if ($this->w_state->get('list.ordering') == 'category_title')
		{
			if ($this->w_state->get('list.direction') == 'asc')
			{
				ksort($this->teams);
			}
			else
			{
				krsort($this->teams);
			}
		}

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

	protected function getTeams($cat)
	{
		if ($cat->id && ($cat->id == $this->exclude))
		{
			return;
		}
		if ($cat->numitems)
		{
			$this->teams[$cat->title]	= $cat;
		}
		if ($cat->hasChildren())
		{
			$children = $cat->getChildren();
			foreach ($children as $child)
			{
				$this->getTeams($child);
			}
		}
	}
}