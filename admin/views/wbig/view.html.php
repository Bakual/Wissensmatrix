<?php
// No direct access
defined('_JEXEC') or die;

/**
 * View to edit a wbig.
 *
 * @package		Wissensmatrix.Administrator
 */
class WissensmatrixViewWbig extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		$canDo		= WissensmatrixHelper::getActions();
		JToolbarHelper::title(JText::sprintf('COM_WISSENSMATRIX_PAGE_'.($checkedOut ? 'VIEW' : ($isNew ? 'ADD' : 'EDIT')), JText::_('COM_WISSENSMATRIX_WBIGS_TITLE'), JText::_('COM_WISSENSMATRIX_WBIG')), 'wbigs');

		// Built the actions for new and existing records.
		if ($isNew)
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::apply('wbig.apply');
				JToolBarHelper::save('wbig.save');
				JToolbarHelper::save2new('wbig.save2new');
			}
			JToolBarHelper::cancel('wbig.cancel');
		}
		else
		{
			// Can't save the record if it's checked out.
			if (!$checkedOut)
			{
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId))
				{
					JToolBarHelper::apply('wbig.apply');
					JToolBarHelper::save('wbig.save');

					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ($canDo->get('core.create'))
					{
						JToolbarHelper::save2new('wbig.save2new');
					}
				}
			}

			// If checked out, we can still save to copy
			if ($canDo->get('core.create'))
			{
				JToolbarHelper::save2copy('wbig.save2copy');
			}

			JToolbarHelper::cancel('wbig.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}