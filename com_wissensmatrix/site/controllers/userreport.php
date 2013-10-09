<?php
defined('_JEXEC') or die;

/**
 * @package     Wissensmatrix
 */
class WissensmatrixControllerUserreport extends JControllerLegacy
{
	/**
	 * Method to request a userreport.
	 *
	 * @since	1.0
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$app	= JFactory::getApplication();

		// Get and sanitize hash
		$hash	= $this->input->get->get('hash');
		if (!$hash || !preg_match('/^[0-9a-f]{32}$/', $hash))
		{
			$app->redirect(JURI::root(), JText::_('COM_WISSENSMATRIX_USERREPORT_HASH_INVALID'), 'error');
		}
		// Get and sanitize uid
		$uid	= strtoupper($this->input->get->get('mit'));
		if (!$uid || !preg_match('/^U\d{6}/', $uid))
		{
			$app->redirect(JURI::root(), JText::sprintf('COM_WISSENSMATRIX_USERREPORT_USERID_INVALID', $uid), 'error');
		}

		// Validate hash and userid
		$db		= JFactory::getDbo();

		$query	= $db->getQuery(true);
		$query->select('id, uid, hash, date');
		$query->from($db->quoteName('#__wissensmatrix_userreport'));
		$query->where($db->quoteName('hash').' = '.$db->quote($hash));
		$query->where($db->quoteName('uid').' = '.$db->quote($uid));
		$query->where($db->quoteName('state').' = 1');
		$query->order($db->quoteName('date').' DESC');

		$db->setQuery($query, 0, 1);
		$row	= $db->loadObject();

		if (!$row)
		{
			$app->redirect(JURI::root(), JText::_('JGLOBAL_RESSOURCE_NOT_FOUND'), 'error');
		}

		if (strtotime($row['date']) < strtotime('-1 week'))
		{
			// Link timed out
			$query	= $db->getQuery(true);
			$query->update($db->quoteName('tbl_user_report'));
			$query->set($db->quoteName('state').' = 0');
			$query->where($db->quoteName('date').' < (DATE_SUB(NOW(), INTERVAL 1 WEEK)');

			$db->setQuery($query);
			$db->execute();
			$app->redirect(JURI::root(), JText::_('COM_WISSENSMATRIX_USERREPORT_LINK_TIMED_OUT'), 'error');
		}

		return parent::display($cachable = false, $urlparams = array());
	}

	/**
	 * Method to request a userreport.
	 *
	 * @since	1.0
	 */
	public function request()
	{
		$uid	= strtoupper($this->input->post->get('uid'));

		$this->setRedirect('index.php');

		// Validate UserID
		if (!$uid || !preg_match('/^U\d{6}/', $uid))
		{
			$this->setMessage(JText::sprintf('COM_WISSENSMATRIX_USERREPORT_USERID_INVALID', $uid), 'error');
			return false;
		}

		$model	= $this->getModel();
		$worker	= $model->getWorkerByUid($uid);

		if (!$worker)
		{
			$this->setMessage(JText::sprintf('COM_WISSENSMATRIX_USERREPORT_USERID_NOT_FOUND', $uid), 'error');
			return false;
		}

		$this->updateUserReport($worker->uid);
	}

	/**
	 * Method to update the user_report table
	 *
	 * @since	1.0
	 */
	private function updateUserReport($uid)
	{
		if (!$uid)
		{
			$this->setMessage(JText::sprintf('COM_WISSENSMATRIX_USERREPORT_USERID_INVALID', $uid), 'error');
			return false;
		}

		$db		= JFactory::getDbo();

		// Set state for old requests for this user to 0
		$query	= $db->getQuery(true);
		$query->update($db->quoteName('#__wissensmatrix_userreport'));
		$query->set($db->quoteName('state').' = 0');
		$query->where($db->quoteName('uid').' = '.$db->quote($uid));
		$db->setQuery($query);
		$db->execute();

		// Create a random hash and insert new request
		$hash		= md5(uniqid(mt_rand(), true));
		$row		= new stdClass();
		$row->uid	= $uid;
		$row->hash	= $hash;
		$row->state	= 1;
		$db->insertObject('#__wissensmatrix_userreport', $row);

		// Create Mail
		$mailer	= JFactory::getMailer();

		// Set sender from Global Configuration
		$config	= JFactory::getConfig();
		$sender	= array( 
			$config->get('config.mailfrom'),
			$config->get('config.fromname')
		);
		$mailer->setSender($sender);

		// Add recipient
		$email	= $uid.'@sbb.ch';
		$mailer->addRecipient($email);

		// Set Subject
		$mailer->setSubject('Wissensmatrix - Userreport');

		// Todo: Translate message with JText
		$url	= JRoute::_(JURI::Root().'index.php?option=com_wissensmatrix&view=userreport&hash='.$hash.'&mit='.$uid);
		$body	= JText::sprintf('COM_WISSENSMATRIX_USERREPORT_EMAIL_BODY', $url);
		$mailer->setBody($body);

		$mailer->isHTML(true);
		$mailer->Encoding = 'base64';

		// Optional file attached, maybe we can directly send the PDF instead of sending a link.
		// $mailer->addAttachment(JPATH_COMPONENT.'/assets/document.pdf');

		// Optionally add embedded image
		// $mailer->AddEmbeddedImage( JPATH_COMPONENT.'/assets/logo128.jpg', 'logo_id', 'logo.jpg', 'base64', 'image/jpeg' );

		// Send Mail
		$sent	= $mailer->Send();
		if ($sent === true)
		{
			$this->setMessage(JText::sprintf('COM_WISSENSMATRIX_USERREPORT_MAIL_SENT', $uid.'@sbb.ch'));
			return true;
		}
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param	string	$name	The model name. Optional.
	 * @param	string	$prefix	The class prefix. Optional.
	 * @param	array	$config	Configuration array for model. Optional.
	 *
	 * @return	object	The model.
	 *
	 * @since	1.5
	 */
	public function getModel($name = 'worker', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}