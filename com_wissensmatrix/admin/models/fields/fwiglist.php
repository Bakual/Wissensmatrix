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
class JFormFieldFwiglist extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'Fwiglist';
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

		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		$lang = substr(JFactory::getLanguage()->getTag(), 0, 2);

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('fwigs.id AS value');
		$query->select('fwigs.title_' . $lang . ' AS text');
		$query->from('#__wissensmatrix_fachwissengruppe AS fwigs');
		$query->join('LEFT', '#__categories AS c_fwigs ON c_fwigs.id = fwigs.catid');
		$query->where('(fwigs.catid = 0 OR (c_fwigs.access IN (' . $groups . ') AND c_fwigs.published = 1))');
		$query->order('text ASC');

		if ($mit_id = (int) $this->element['mit_id'])
		{
			$subquery = $db->getQuery(true);
			$subquery->select('DISTINCT fwig_id');
			$subquery->from('#__wissensmatrix_mit_fwi AS zfwi');
			$subquery->join('LEFT', '#__wissensmatrix_fachwissen AS fwis ON zfwi.fwi_id = fwis.id');
			$subquery->where('zfwi.mit_id = ' . $mit_id);
			$query->where('fwigs.id NOT IN (' . $subquery . ')');
		}

		// Get the options.
		$db->setQuery($query);

		$items = $db->loadObjectList();

		foreach ($items as $item)
		{
			$options[] = JHtml::_('select.option', $item->value, $item->text);
		}

		// Check for a database error.
		if ($db->getErrorNum())
		{
			JError::raiseWarning(500, $db->getErrorMsg());
		}

		array_unshift($options, JHtml::_('select.option', '0', JText::_('COM_WISSENSMATRIX_FIELD_FWIG_ID_SELECT')));

		return $options;
	}
}
