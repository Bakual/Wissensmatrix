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
}