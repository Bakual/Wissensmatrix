<?php
defined('_JEXEC') or die;

/**
 * HTML View class for the Wissensmatrix Component
 */
class WissensmatrixViewListwbis extends JViewLegacy
{
	function display($tpl = null)
	{
		$this->state      = $this->get('State');
		$this->state->set('list.start', 0);
		$this->state->set('list.limit', 0);
		$this->state->set('list.ordering', 'wbig_title');
		$this->state->set('list.direction', 'ASC');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		$this->params = $this->state->get('params');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		parent::display($tpl);
	}
}