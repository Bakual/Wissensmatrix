<?php
defined('_JEXEC') or die;

class WissensmatrixViewFwigs extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		if ($this->getLayout() !== 'modal')
		{
			WissensmatrixHelper::addSubmenu('fwigs');
		}

		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');

		$db = JFactory::getDbo();
		foreach ($this->items as $item)
		{
			if (!$item->alias)
			{
				$query	= $db->getQuery(true);
				$query->UPDATE('#__wissensmatrix_fachwissengruppe');
				$query->SET('alias = "'.JApplication::stringURLSafe($item->title).'"');
				$query->WHERE('id = '.$item->id);
				$db->setQuery($query);
				$db->query();
			}
		}

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		$this->addFilters();
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		$canDo 	= WissensmatrixHelper::getActions();

		JToolBarHelper::title(JText::_('COM_WISSENSMATRIX_FWIGS_TITLE'), 'fwigs');

		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('fwig.add','JTOOLBAR_NEW');
		}

		if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own'))) {
			JToolBarHelper::editList('fwig.edit','JTOOLBAR_EDIT');
		}

		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::divider();
			JToolBarHelper::custom('fwigs.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('fwigs.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
			if ($this->state->get('filter.state') != 2) {
				JToolBarHelper::archiveList('fwigs.archive','JTOOLBAR_ARCHIVE');
			} else {
				JToolBarHelper::unarchiveList('fwigs.publish', 'JTOOLBAR_UNARCHIVE');
			}
			JToolBarHelper::checkin('fwigs.checkin');
		}

		if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'fwigs.delete','JTOOLBAR_EMPTY_TRASH');
			JToolBarHelper::divider();
		} else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('fwigs.trash','JTOOLBAR_TRASH');
			JToolBarHelper::divider();
		}

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		// Add a batch button
		if ($canDo->get('core.edit'))
		{
			$title = JText::_('JTOOLBAR_BATCH');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small\">
						<i class=\"icon-checkbox-partial\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_wissensmatrix', 650, 900);
		}
	}

	/**
	 * Add the filters.
	 */
	protected function addFilters()
	{
		JHtmlSidebar::setAction('index.php?option=com_wissensmatrix&view=fwigs');

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_published',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true)
		);

		JHtmlSidebar::addFilter(
			JText::_('COM_WISSENSMATRIX_FIELD_BOOL_SELECT'),
			'filter_bool',
			JHtml::_('select.options', array(0 => JText::_('COM_WISSENSMATRIX_UNBOOL'), 1 => JText::_('COM_WISSENSMATRIX_BOOL')), 'value', 'text', $this->state->get('filter.bool'), true)
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_CATEGORY'),
			'filter_category_id',
			JHtml::_('select.options', JHtml::_('category.options', 'com_wissensmatrix'), 'value', 'text', $this->state->get('filter.category_id'))
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_LANGUAGE'),
			'filter_language',
			JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'))
		);
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
			'fwigs.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'fwigs.state' => JText::_('JSTATUS'),
			'fwigs.title' => JText::_('JGLOBAL_TITLE'),
			'category_title' => JText::_('JCATEGORY'),
			'fwigs.hits' => JText::_('JGLOBAL_HITS'),
			'language' => JText::_('JGRID_HEADING_LANGUAGE'),
			'fwigs.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}