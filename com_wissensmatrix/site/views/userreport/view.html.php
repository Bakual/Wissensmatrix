<?php
defined('_JEXEC') or die;

/**
 * HTML Article View class for the Wissensmatrix component
 *
 */
class WissensmatrixViewUserreport extends JViewLegacy
{
	public function display($tpl = null)
	{
		$app	= JFactory::getApplication();

		// Get Params
		$state			= $this->get('State');
		$this->params	= $state->get('params');

		// Get and sanitize uid
		$uid	= strtoupper($this->input->get->get('mit'));
		if (!$uid || !preg_match('/^U\d{6}/', $uid))
		{
			$app->redirect(JURI::root(), JText::sprintf('COM_WISSENSMATRIX_USERREPORT_USERID_INVALID', $uid), 'error');
		}

		// Get id from uid
		$model			= $this->getModel();
		$worker			= $model->getWorkerByUid($uid);
		$state->set('worker.id', $worker->id);
		$this->item		= $this->get('Item');
		if(!$this->item)
		{
			$app->redirect(JURI::root(), JText::_('JGLOBAL_RESOURCE_NOT_FOUND'), 'error');
		}

		// Get wbis data from the wbis model
		$wbi_model	= $this->getModel('Wbis');
		$wbi_model->setState('worker.id', $worker->id);
		$wbi_model->setState('userreport', 'true');
		$this->state_wbi	= $wbi_model->getState();
		$this->wbis			= $wbi_model->getItems();

		// Get fwis data from the fwis model
		$fwi_model	= $this->getModel('Fwis');
		$fwi_model->setState('worker.id', $worker->id);
		$fwi_model->setState('userreport', 'true');
		$this->state_fwi	= $fwi_model->getState();
		$this->fwis			= $fwi_model->getItems();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->pageclass_sfx	= htmlspecialchars($this->params->get('pageclass_sfx'));
		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app	= JFactory::getApplication();
		$menus	= $app->getMenu();
		$title	= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', JText::_('COM_WISSENSMATRIX_USERREPORT'));
		}

		$title = $this->params->def('page_title', JText::_('COM_WISSENSMATRIX_USERREPORT'));
		if ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		$pathway = $app->getPathWay();
		$pathway->addItem($title, '');

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'title' => JText::_('JGLOBAL_TITLE'),
			'zwbi.date' => JText::_('JDATE'),
			'zwbi.status_id' => JText::_('JSTATUS'),
		);
	}
}
