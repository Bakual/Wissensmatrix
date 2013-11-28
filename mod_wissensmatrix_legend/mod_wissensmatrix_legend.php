<?php
/**
 * @package     Wissensmatrix
 * @subpackage  mod_wissensmatrix_userreport
 *
 * @copyright   Copyright (C) 2013 Thomas Hunziker
 * @license     GNU General Public License version 2
 */

defined('_JEXEC') or die;

$jinput	= JFactory::getApplication()->input;

if ($jinput->get('option') != 'com_wissensmatrix')
{
	return;
}

$view	= $jinput->get('view');
switch ($view)
{ 
	case 'reportfwiglevels':
	case 'reportfwigteam':
	case 'reportfwiteam':
		$layout	= 'default';
		break;
	case 'reportfwiglevelssummary':
		$layout	= 'levelssummary';
		break;
	case 'reportfwigdiffsummary':
		$layout	= 'diffsummary';
		break;
	default:
		return;
}
$moduleclass_sfx	= htmlspecialchars($params->get('moduleclass_sfx'));

JHTML::addIncludePath(JPATH_SITE.'components/com_wissensmatrix/helpers');

require JModuleHelper::getLayoutPath('mod_wissensmatrix_legend', $layout);
