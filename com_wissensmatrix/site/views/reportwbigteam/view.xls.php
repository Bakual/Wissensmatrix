<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * HTML View class for the Wissensmatrix Component
 */
class WissensmatrixViewReportwbigteam extends JViewLegacy
{
	function display($tpl = null)
	{
		// Get some data from the model
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');

		// Get Workers for selected teams
		$this->workermodel = $this->getModel('Workers');
		$this->w_state     = $this->workermodel->getState();
		$this->w_state->set('list.start', 0);
		$this->w_state->set('list.limit', 0);
		$this->parent = $this->workermodel->getParent();
		$workers      = $this->workermodel->getItems();
		foreach ($workers as $worker)
		{
			$ids[] = $worker->id;
		}

		// Get some data from the wbis model
		$this->wbismodel  = $this->getModel('Wbis');
		$this->wbis_state = $this->wbismodel->getState();
		$this->wbis_state->set('wbig.id', JFactory::getApplication()->input->get('id', 0, 'int'));
		$this->wbis_state->set('filter.search', '');
		$this->wbis_state->set('worker.id', $ids);
		$this->wbis_state->set('filter.zwbistate', $this->w_state->get('filter.zwbistate', 0));

		$this->items = $this->wbismodel->getItems();

		$this->params = $this->state->get('params');

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