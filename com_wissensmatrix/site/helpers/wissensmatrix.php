<?php
defined('_JEXEC') or die('Restricted access');

/**
 * Wissensmatrix Component Wissensmatrix Helper
 */
class WissensmatrixHelperWissensmatrix
{
	public static function getDiffClass($ist, $soll)
	{
		$diff = $soll - $ist;
		if ($diff > 3)
		{
			return 'important';
		}
		if ($diff < 0)
		{
			return 'success';
		}
		switch ($diff)
		{
			default:
				return 'default';
			case 1:
				return 'info';
			case 2:
				return 'warning';
			case 3:
				return 'important';
		}
	}

	/* Get a class for a percent value 
	 * $value	int
	 * return	string class
	 */
	public static function getPercentClass($value)
	{
		switch ($value)
		{
			case 0:
			case ($value < 50):
				return 'important';
			case ($value < 75):
				return 'warning';
			case ($value < 100):
				return 'info';
			case 100:
				return 'default';
			case ($value > 100):
				return 'success';
		}
	}

	/**
	 * Retrieves the default team for the user.
	 *
	 * return  integer
	 */
	public static function getDefaultTeam()
	{
		$user  = JFactory::getUser();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('profile_value'))
			->from($db->quoteName('#__user_profiles'))
			->where($db->quoteName('profile_key') . ' = ' . $db->quote('wissensmatrix.team'))
			->where($db->quoteName('user_id') . ' = ' . $user->id);
		$db->setQuery($query);

		return (int) json_decode($db->loadResult());
	}
}