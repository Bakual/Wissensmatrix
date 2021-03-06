<?php
defined('_JEXEC') or die;

/**
 * An plugin which adds some Wissensmatrix specific settings to the user profile.
 * Based on the example user profile plugin.
 *
 * @since  3.0
 */
class PlgUserWissensmatrix extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * @param   object &$subject The object to observe
	 * @param   array  $config   An array that holds the plugin configuration
	 *
	 * @since   1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		JFormHelper::addFieldPath(__DIR__ . '/fields');
	}

	/**
	 * Runs on content preparation
	 *
	 * @param   string $context The context for the data
	 * @param   object $data    An object containing the data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_users.profile', 'com_users.user', 'com_users.registration', 'com_admin.profile')))
		{
			return true;
		}

		if (is_object($data))
		{
			$userId = isset($data->id) ? $data->id : 0;

			if (!isset($data->wissensmatrix) && $userId)
			{
				// Load the profile data from the database.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('profile_key, profile_value')
					->from('#__user_profiles')
					->where('user_id = ' . (int) $userId)
					->where('profile_key LIKE "wissensmatrix.%"')
					->order('ordering');
				$db->setQuery($query);

				try
				{
					$results = $db->loadRowList();
				}
				catch (RuntimeException $e)
				{
					$this->_subject->setError($e->getMessage());

					return false;
				}

				// Merge the profile data.
				$data->wissensmatrix = array();

				foreach ($results as $v)
				{
					$k                       = str_replace('wissensmatrix.', '', $v[0]);
					$data->wissensmatrix[$k] = json_decode($v[1], true);

					if ($data->wissensmatrix[$k] === null)
					{
						$data->wissensmatrix[$k] = $v[1];
					}
				}

				if (!JHtml::isRegistered('users.team'))
				{
					JHtml::register('users.team', array(__CLASS__, 'team'));
				}
			}
		}

		return true;
	}

	/**
	 * Adds additional fields to the user editing form
	 *
	 * @param   JForm $form The form to be altered.
	 * @param   mixed $data The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		// Check we are manipulating a valid form.
		$name = $form->getName();

		if (!in_array($name, array('com_admin.profile', 'com_users.user', 'com_users.profile', 'com_users.registration')))
		{
			return true;
		}

		// Add the registration fields to the form.
		JForm::addFormPath(__DIR__ . '/forms');
		$form->loadFile('default', false);

		return true;
	}

	/**
	 * Method is called before user data is stored in the database
	 *
	 * @param   array   $user  Holds the old user data.
	 * @param   boolean $isnew True if a new user is stored.
	 * @param   array   $data  Holds the new user data.
	 *
	 * @return    boolean
	 *
	 * @since   3.1
	 * @throws    InvalidArgumentException on invalid date.
	 */
	public function onUserBeforeSave($user, $isnew, $data)
	{
		// Check that the date is valid.
		return true;
	}

	/**
	 * Saves user profile data
	 *
	 * @param   array   $data   entered user data
	 * @param   boolean $isNew  true if this is a new user
	 * @param   boolean $result true if saving the user worked
	 * @param   string  $error  error message
	 *
	 * @return bool
	 */
	public function onUserAfterSave($data, $isNew, $result, $error)
	{
		$userId = Joomla\Utilities\ArrayHelper::getValue($data, 'id', 0, 'int');

		if (!$userId || !$result)
		{
			return true;
		}

		if (!empty($data['wissensmatrix']))
		{
			try
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true)
					->delete($db->quoteName('#__user_profiles'))
					->where($db->quoteName('user_id') . ' = ' . (int) $userId)
					->where($db->quoteName('profile_key') . ' LIKE ' . $db->quote('wissensmatrix.%'));
				$db->setQuery($query);
				$db->execute();

				$tuples = array();
				$order  = 1;

				foreach ($data['wissensmatrix'] as $k => $v)
				{
					$tuples[] = '(' . $userId . ', ' . $db->quote('wissensmatrix.' . $k) . ', ' . $db->quote(json_encode($v)) . ', ' . ($order++) . ')';
				}

				$db->setQuery('INSERT INTO #__user_profiles VALUES ' . implode(', ', $tuples));
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$this->_subject->setError($e->getMessage());

				return false;
			}
		}

		return true;
	}

	/**
	 * Remove all user profile information for the given user ID
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   array   $user    Holds the user data
	 * @param   boolean $success True if user was succesfully stored in the database
	 * @param   string  $msg     Message
	 *
	 * @return  boolean
	 */
	public function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$userId = Joomla\Utilities\ArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId)
		{
			try
			{
				$db = JFactory::getDbo();
				$db->setQuery(
					'DELETE FROM #__user_profiles WHERE user_id = ' . $userId .
					" AND profile_key LIKE 'wissensmatrix.%'"
				);

				$db->execute();
			}
			catch (Exception $e)
			{
				$this->_subject->setError($e->getMessage());

				return false;
			}
		}

		return true;
	}

	/**
	 * returns the team name instead of the id
	 *
	 * @param   string $value url to use
	 *
	 * @return mixed|string
	 */
	public static function team($value)
	{
		if ($value)
		{
			$table = JTable::getInstance('category');
			$table->load($value);

			return $table->title;
		}
	}
}
