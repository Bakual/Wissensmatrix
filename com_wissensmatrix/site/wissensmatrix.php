<?php
defined('_JEXEC') or die;

require_once(JPATH_COMPONENT.'/helpers/route.php');
require_once(JPATH_COMPONENT.'/helpers/wissensmatrix.php');

$controller	= JControllerLegacy::getInstance('Wissensmatrix');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();