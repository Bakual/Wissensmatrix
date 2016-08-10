<?php
/**
 * @package     Wissensmatrix
 * @subpackage  mod_wissensmatrix_birthday
 *
 * @copyright   Copyright (C) 2016 Thomas Hunziker
 * @license     GNU General Public License version 2
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';

$list            = ModWissensmatrixBirthdayHelper::getList($params);

if (!$list)
{
	return;
}

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require JModuleHelper::getLayoutPath('mod_wissensmatrix_birthday', $params->get('layout', 'default'));
