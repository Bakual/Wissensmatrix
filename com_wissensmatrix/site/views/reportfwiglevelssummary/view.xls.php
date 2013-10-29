<?php
defined('_JEXEC') or die;
jimport( 'joomla.application.component.view');
/**
 * HTML View class for the Wissensmatrix Component
 */
class WissensmatrixViewReportfwiglevelssummary extends JViewLegacy
{
	function display($tpl = null)
	{
		// Get some data from the model
		$this->model		= $this->getModel();
		$this->state		= $this->get('State');
		$this->state->set('list.start', 0);
		$this->state->set('list.limit', 0);
		$this->items		= $this->get('Items');

		$this->fwis_model	= $this->getModel('fwis');
		$this->fwis_state	= $this->fwis_model->getState();
		$this->fwis_state->set('list.start', 0);
		$this->fwis_state->set('list.limit', 0);
		$this->levels		= $this->fwis_model->getLevels();
		foreach ($this->levels as $key => $level)
		{
			$levels[]	= $key;
		}
		$levels = implode(',', $levels);

		foreach ($this->levels as $key => $level)
		{
			if (!$level->value) continue;
			$this->ist[$key]		= $this->model->getLevelSummary($key, $levels, false, true);
			$this->soll[$key]		= $this->model->getLevelSummary($key, $levels, true, true);
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
}