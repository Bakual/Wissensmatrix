<?php
defined('_JEXEC') or die;

/**
 * HTML View class for the Wissensmatrix Component
 */
class WissensmatrixViewExportsnow extends JViewLegacy
{
	function display($tpl = null)
	{
		// Get some data from the model
		$this->model = $this->getModel();
		$this->state = $this->get('State');
		$this->state->set('list.start', 0);
		$this->state->set('list.limit', 0);
		$this->state->set('list.ordering', 'fwig_title, title');
		$this->items = $this->get('Items');

		// Get Workers for selected teams
		$this->workersmodel = $this->getModel('Workers');
		$this->w_state     = $this->workersmodel->getState();
		$this->w_state->set('list.start', 0);
		$this->w_state->set('list.limit', 0);
		$this->workers = $this->workersmodel->getItems();


		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->setLayout('xls');

		parent::display($tpl);
	}

	public function num2alpha($n)
	{
		for($r = ""; $n >= 0; $n = intval($n / 26) - 1)
			$r = chr($n%26 + 0x41) . $r;
		return $r;
	}
}