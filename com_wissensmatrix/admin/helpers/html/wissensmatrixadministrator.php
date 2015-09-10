<?php
defined('_JEXEC') or die;

JLoader::register('WissensmatrixHelper', JPATH_ADMINISTRATOR . '/components/com_wissensmatrix/helpers/wissensmatrix.php');

abstract class JHtmlWissensmatrixAdministrator
{
	/**
	 * @param    int $value The state value
	 * @param    int $i
	 */
	public static function bool($value = 0, $i, $canChange = true)
	{
		JHtml::_('bootstrap.tooltip');

		// Array of image, task, title, action
		$states = array(
			0 => array('star-empty', 'fwigs.bool', 'COM_WISSENSMATRIX_UNBOOL', 'COM_WISSENSMATRIX_TOGGLE_TO_BOOL'),
			1 => array('star', 'fwigs.unbool', 'COM_WISSENSMATRIX_BOOL', 'COM_WISSENSMATRIX_TOGGLE_TO_UNBOOL'),
		);
		$state  = JArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon   = $state[0];
		if ($canChange)
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip' . ($value == 1 ? ' active' : '') . '" title="' . JText::_($state[3]) . '"><i class="icon-'
				. $icon . '"></i></a>';
		}

		return $html;
	}
}
