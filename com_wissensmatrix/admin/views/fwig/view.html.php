<?php
// No direct access
defined('_JEXEC') or die;

/**
 * View to edit a Fachwissen.
 *
 * @package        Wissensmatrix.Administrator
 */
class WissensmatrixViewFwig extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since    1.6
	 */
	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', true);

		$user   = JFactory::getUser();
		$userId = $user->get('id');
		$isNew  = ($this->item->id == 0);
		$canDo  = WissensmatrixHelper::getActions();

		JToolBarHelper::title(JText::_('COM_WISSENSMATRIX_FWIGS_TITLE'), 'fwis');

		// Built the actions for new and existing records.
		if ($isNew)
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::apply('fwig.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('fwig.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('fwig.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}

			JToolBarHelper::cancel('fwig.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
			if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId))
			{
				JToolBarHelper::apply('fwig.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('fwig.save', 'JTOOLBAR_SAVE');

				// We can save this record as copy, but check the create permission first.
				if ($canDo->get('core.create'))
				{
					JToolBarHelper::custom('fwig.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
					JToolBarHelper::custom('fwig.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
				}
			}

			JToolBarHelper::cancel('fwig.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}