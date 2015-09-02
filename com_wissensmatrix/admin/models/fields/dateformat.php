<?php
defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Dateformat Field class for the Wissensmatrix.
 *
 * @package        Wissensmatrix
 * @since          3.0
 */
class JFormFieldDateformat extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'Dateformat';

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 * @since    1.6
	 */
	public function getOptions()
	{
		// Initialize variables.
		$options     = array();
		$date        = JHTML::Date('', 'Y-m-d H:m:s', true);
		$dateformats = array('DATE_FORMAT_LC', 'DATE_FORMAT_LC1', 'DATE_FORMAT_LC2', 'DATE_FORMAT_LC3', 'DATE_FORMAT_LC4');
		foreach ($dateformats AS $key => $format)
		{
			$options[$key]['value'] = $format;
			$options[$key]['text']  = JHTML::Date($date, JText::_($format), true);
		}

		return $options;
	}
}
