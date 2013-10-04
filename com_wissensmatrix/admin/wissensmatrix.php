<?php
defined('_JEXEC') or die;

// Access check
if (!JFactory::getUser()->authorise('core.manage', 'com_wissensmatrix'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// require helper file
JLoader::register('WissensmatrixHelper', dirname(__FILE__).'/helpers/wissensmatrix.php');

JHTML::stylesheet('administrator/components/com_wissensmatrix/wissensmatrix.css');

// Load languages and merge with fallbacks
// $jlang	= JFactory::getLanguage();
// $jlang->load('com_wissensmatrix', JPATH_COMPONENT, 'en-GB', true);
// $jlang->load('com_wissensmatrix', JPATH_COMPONENT, null, true);

$controller	= JControllerLegacy::getInstance('Wissensmatrix');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();