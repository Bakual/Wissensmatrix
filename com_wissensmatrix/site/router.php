<?php
defined('_JEXEC') or die;

function WissensmatrixBuildRoute(&$query){
	$app		= JFactory::getApplication();
	$segments	= array();
	$view		= '';
	if (isset($query['view'])){
		$segments[] = $query['view'];
		$view = $query['view'];
		unset($query['view']);
	}
	if (isset($query['id'])){
		$segments[] = $query['id'];
		unset($query['id']);
	}
	return $segments;
}

function WissensmatrixParseRoute($segments){
	$vars = array();
	switch ($segments[0]){
		case 'worker':
			$vars['view'] = 'worker';
			$id = explode(':', $segments[1]);
			$vars['id'] = (int)$id[0];
			break;
	}
	return $vars;
}