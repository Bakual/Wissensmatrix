<?php
defined('_JEXEC') or die;
jimport( 'joomla.application.component.view');
/**
 * HTML View class for the Wissensmatrix Component
 */
class WissensmatrixViewReportwbigteam extends JViewLegacy
{
	function display($tpl = null)
	{
		// Get some data from the model
		$this->model		= $this->getModel();
		$this->state		= $this->get('State');
		$this->state->set('wbig.id', JFactory::getApplication()->input->get('id', 0, 'int'));
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');

		// Get Workers for selected teams
		$this->workermodel = $this->getModel('Workers');
		$this->w_state		= $this->workermodel->getState();
		$this->w_state->set('list.start', 0);
		$this->w_state->set('list.limit', 0);
		$this->workers		= $this->workermodel->getItems();
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