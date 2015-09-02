<?php
/**
 * @copyright      Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
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
 * @package        Wissensmatrix
 * @since          4.0
 */
class JFormFieldWbilist extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'Wbilist';
	protected $translateLabel = false;

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 * @since    1.6
	 */
	public function getOptions()
	{
		// Initialize variables.
		$options = array();

		$lang = substr(JFactory::getLanguage()->getTag(), 0, 2);

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('wbis.id AS value');
		$query->select('wbis.title_' . $lang . ' AS text');
		$query->from('#__wissensmatrix_weiterbildung AS wbis');
		$query->select('wbigs.title_' . $lang . ' AS wbig_title');
		$query->join('LEFT', '#__wissensmatrix_weiterbildunggruppe AS wbigs ON wbigs.id = wbis.wbig_id');
		if ($mit_id = (int) $this->element['mit_id'])
		{
			$subquery = $db->getQuery(true);
			$subquery->select('count(1)');
			$subquery->from('#__wissensmatrix_mit_wbi AS zwbi');
			$subquery->where('zwbi.mit_id = ' . $mit_id);
			$subquery->where('zwbi.wbi_id = wbis.id');
			$subquery->group('zwbi.wbi_id');
			$query->select('(' . $subquery . ') as zwbi_count');
		}
		$query->order('wbig_title ASC, text ASC');

		// Get the options.
		$db->setQuery($query);

		$items = $db->loadObjectList();

		$wbig_title = '';
		foreach ($items as $item)
		{
			if ($wbig_title != $item->wbig_title)
			{
				if ($wbig_title)
				{
					$options[] = JHtml::_('select.optgroup', $wbig_title);
				}
				$options[]  = JHtml::_('select.optgroup', $item->wbig_title);
				$wbig_title = $item->wbig_title;
			}
			if ($item->zwbi_count)
			{
				$item->text .= ' &rArr; ' . JText::_('COM_WISSENSMATRIX_WBI_ERFASST');
			}
			$options[] = JHtml::_('select.option', $item->value, $item->text);
		}
		if ($wbig_title)
		{
			$options[] = JHtml::_('select.optgroup', $wbig_title);
		}

		// Check for a database error.
		if ($db->getErrorNum())
		{
			JError::raiseWarning(500, $db->getErrorMsg());
		}

		array_unshift($options, JHtml::_('select.option', '0', JText::_('COM_WISSENSMATRIX_FIELD_WBI_ID_SELECT')));

		return $options;
	}
}
