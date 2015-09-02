<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * HTML View class for the Wissensmatrix Component
 */
class WissensmatrixViewReportfwigdiffsummary extends JViewLegacy
{
	function display($tpl = null)
	{
		// Get some data from the model
		$this->model = $this->getModel();
		$this->state = $this->get('State');
		$this->state->set('list.start', 0);
		$this->state->set('list.limit', 0);
		$this->items = $this->get('Items');

		$this->manko     = $this->model->getDiffSummary(2, true);
		$this->potential = $this->model->getDiffSummary(1, true);
		$this->workers   = $this->model->getDiffSummary(false, true);

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