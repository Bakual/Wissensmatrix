<?php
defined('_JEXEC') or die;

require_once JPATH_COMPONENT_ADMINISTRATOR.'/models/zwbi.php';

/**
 * Wissensmatrix model.
 *
 */
class WissensmatrixModelZwbiform extends WissensmatrixModelZwbi
{
	/**
	 * Get the return URL.
	 *
	 * @return	string	The return URL.
	 * @since	1.6
	 */
	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('a_id');
		$this->setState('zwbi.id', $pk);
		// Add compatibility variable for default naming conventions.
		$this->setState('zwbiform.id', $pk);

		$return = $app->input->get('return', null, 'base64');

		if (!JUri::isInternal(base64_decode($return))) {
			$return = null;
		}

		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params	= $app->getParams();
		$this->setState('params', $params);

		$this->setState('layout', $app->input->get('layout'));
	}
}
