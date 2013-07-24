<?php
/**
 * @package		com_wissensmatrix
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.categories');

/**
 * Wissensmatrix Component Category Tree
 *
 * @static
 * @package		com_wissensmatrix
 */
class WissensmatrixCategories extends JCategories
{
	public function __construct($options = array())
	{
		if (!isset($options['table'])){
			$options['table'] = '#__wissensmatrix_fachwissen';
		}
		if (!isset($options['extension'])){
			$options['extension'] = 'com_wissensmatrix';
		}
		parent::__construct($options);
	}
}