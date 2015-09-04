<?php
defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('category');

/**
 * Provides input for "Team" field
 *
 * @since       3.0
 */
class JFormFieldTeam extends JFormFieldCategory
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.0
	 */
	public $type = 'Team';

	/**
	 * Method to get the teams
	 *
	 * @return  array    The field option objects.
	 *
	 * @since   3.0
	 */
	protected function getOptions()
	{
		$options = parent::getOptions();
		$user    = JFactory::getUser();

		foreach ($options as $i => $option)
		{
			if (!$user->authorise('wissensmatrix.view.worker', 'com_wissensmatrix.category.' . $option->value))
			{
				unset($options[$i]);
			}
		}

		return $options;
	}
}
