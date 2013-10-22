<?php
defined('_JEXEC') or die;
jimport( 'joomla.application.component.view');
/**
 * HTML View class for the Wissensmatrix Component
 */
class WissensmatrixViewReportfwiglevels extends JViewLegacy
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

		$this->setLayout('xls');

		parent::display($tpl);
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