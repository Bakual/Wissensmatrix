<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * HTML View class for the Wissensmatrix Component
 */
class WissensmatrixViewReportfwigteam extends JViewLegacy
{
	function display($tpl = null)
	{
		// Get Fwig Id
		$id = JFactory::getApplication()->input->get('id', 0, 'int');
		if (!$id)
		{
			return JError::raiseError(404, JText::_('JGLOBAL_RESOURCE_NOT_FOUND'));
		}

		// Get some data from the model
		$this->model = $this->getModel();
		$this->state = $this->get('State');
		$this->state->set('fwig.id', $id);
		$this->state->set('list.start', 0);
		$this->state->set('list.limit', 0);
		$this->items = $this->get('Items');

		// Get Workers for selected teams
		$this->workermodel = $this->getModel('Workers');
		$this->w_state     = $this->workermodel->getState();
		$this->w_state->set('list.start', 0);
		$this->w_state->set('list.limit', 0);
		$this->workers = $this->workermodel->getItems();


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