<?php
defined('_JEXEC') or die;

class WissensmatrixViewMain extends JViewLegacy
{
	function display($tpl = null)
	{
		$params = JComponentHelper::getParams('com_wissensmatrix');
		if (!$params->get('date_format'))
		{
			JError::raiseWarning(100, JText::_('COM_WISSENSMATRIX_NOTSAVED'));
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_WISSENSMATRIX'), 'fwis');

		$canDo = WissensmatrixHelper::getActions();
		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::divider();
			JToolBarHelper::preferences('com_wissensmatrix', 650, 900);
		}
	}
}