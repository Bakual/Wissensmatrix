<?php
// No direct access
defined('_JEXEC') or die;

/**
 * Serie Table class
 *
 * @package        Wissensmatrix.Administrator
 */
class WissensmatrixTableZfwi extends JTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__wissensmatrix_mit_fwi', 'id', $db);
	}

	public function store($updateNulls = false)
	{
		// Attempt to store the user data.
		return parent::store($updateNulls);
	}
}