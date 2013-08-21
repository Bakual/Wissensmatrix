<?php
defined('_JEXEC') or die;
jimport( 'joomla.application.component.view');
/**
 * HTML View class for the Wissensmatrix Component
 */
class WissensmatrixViewReportfwigteam extends JViewLegacy
{
	function display($tpl = null)
	{
		// Set some states in the model
		$this->model		= $this->getModel();
		$this->model->setState('fwig.id', JFactory::getApplication()->input->get('id', 0, 'int'));

		// Get some data from the model
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');

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

		$this->setLayout('xls');

		parent::display($tpl);
	}
}