<?php
/**
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Serieslist Field class for the Wissensmatrix.
 * Based on the Bannerlist field from com_banners
 *
 * @package		Wissensmatrix
 * @since		4.0
 */
class JFormFieldFwilist extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Fwilist';
	protected $translateLabel = false;

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	public function getOptions()
	{
		// Initialize variables.
		$options = array();

		$db		= JFactory::getDbo();

		$query	= $db->getQuery(true);
		$query->select('fwis.id AS value');
		$query->select('fwis.title_de AS text');
		$query->from('#__wissensmatrix_fachwissen AS fwis');
		$query->select('fwigs.title_de AS fwig_title');
		$query->join('LEFT', '#__wissensmatrix_fachwissengruppe AS fwigs ON fwigs.id = fwis.fwig_id');
		$query->order('fwig_title ASC, text ASC');

		// Get the options.
		$db->setQuery($query);

		$items = $db->loadObjectList();

		$fwig_title = '';
		foreach ($items as $item)
		{
			if ($fwig_title != $item->fwig_title)
			{
				if ($fwig_title)
				{
					$options[] = JHtml::_('select.optgroup', $fwig_title);
				}
				$options[] = JHtml::_('select.optgroup', $item->fwig_title);
				$fwig_title = $item->fwig_title;
			}
			$options[] = JHtml::_('select.option', $item->value, $item->text);
		}
		if ($fwig_title)
		{
			$options[] = JHtml::_('select.optgroup', $fwig_title);
		}

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}

		array_unshift($options, JHtml::_('select.option', '0', JText::_('COM_WISSENSMATRIX_FIELD_FWI_ID_SELECT')));

		return $options;
	}
}
