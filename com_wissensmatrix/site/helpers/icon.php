<?php
defined('_JEXEC') or die;

/**
 * Wissensmatrix Component HTML Helper
 */
class JHtmlIcon
{
	public static function create($category, $params)
	{
		$uri = JURI::getInstance();

		$url = 'index.php?option=com_wissensmatrix&task=worker.add&return='.base64_encode($uri).'&a_id=0&catid='.$category->id;

		$text = '<i class="icon-plus"></i> ' . JText::_('JNEW') . '&#160;';

		$button = JHtml::_('link', JRoute::_($url), $text, 'class="btn btn-primary"');

		$output = '<span class="hasTip" title="'.JText::_('COM_WISSENSMATRIX_CREATE_WORKER').'">'.$button.'</span>';
		return $output;
	}

	/**
	 * Display an edit icon for the item.
	 *
	 * This icon will not display in a popup window, nor if the item is trashed.
	 * Edit access checks must be performed in the calling code.
	 *
	 * @param	object	$item		The item in question.
	 * @param	object	$params		The item parameters
	 * @param	array	$attribs	Not used??
	 *
	 * @return	string	The HTML for the item edit icon.
	 * @since	1.6
	 */
	public static function edit($item, $params, $attribs = array())
	{
		$user	= JFactory::getUser();
		$userId	= $user->get('id');
		$uri	= JURI::getInstance();

		// Ignore if in a popup window.
		if ($params && $params->get('popup'))
		{
			return;
		}

		// Ignore if the state is negative (trashed).
		if ($item->state < 0)
		{
			return;
		}

		JHtml::_('behavior.tooltip');

		// Show checked_out icon if the item is checked out by a different user
		if (property_exists($item, 'checked_out') && property_exists($item, 'checked_out_time') && $item->checked_out > 0 && $item->checked_out != $user->get('id'))
		{
			$checkoutUser = JFactory::getUser($item->checked_out);
			$button = JHtml::_('image', 'system/checked_out.png', null, null, true);
			$date = JHtml::_('date', $item->checked_out_time);
			$tooltip = JText::_('JLIB_HTML_CHECKED_OUT').' :: '.JText::sprintf('COM_WISSENSMATRIX_CHECKED_OUT_BY', $checkoutUser->name).' <br /> '.$date;
			return '<span class="hasTip" title="'.htmlspecialchars($tooltip, ENT_COMPAT, 'UTF-8').'">'.$button.'</span>';
		}

		$url	= 'index.php?option=com_wissensmatrix&task=worker.edit&a_id='.$item->id.'&return='.base64_encode($uri);

		if ($item->state == 0)
		{
			$overlib = JText::_('JUNPUBLISHED');
		}
		else
		{
			$overlib = JText::_('JPUBLISHED');
		}

		if ($item->created != '0000-00-00 00:00:00')
		{
			$date = JHtml::_('date', $item->created);

			$overlib .= '&lt;br /&gt;';
			$overlib .= $date;
		}
		if ($item->author)
		{
			$overlib .= '&lt;br /&gt;';
			$overlib .= JText::sprintf('COM_WISSENSMATRIX_WRITTEN_BY', htmlspecialchars($item->author, ENT_COMPAT, 'UTF-8'));
		}

		$icon	= $item->state ? 'edit' : 'eye-close';
		$text	= '<i class="hasTip icon-'.$icon.' tip" title="'.JText::_('COM_WISSENSMATRIX_EDIT_ITEM').' :: '.$overlib.'"></i>';
		if (empty($attribs['hide_text']))
		{
			$text	.= ' '.JText::_('JGLOBAL_EDIT');
		}

		$output = JHtml::_('link', JRoute::_($url), $text).' &nbsp;';

		return $output;
	}

	public static function editz($item, $params, $attribs = array())
	{
		$uri	= JRoute::_('index.php?option=com_wissensmatrix&view=close&tmpl=component');

		$id		= ($attribs['type'] == 'wbi') ? $item->zwbi_id : $item->fwig_id;
		$class	= isset($attribs['class']) ? $attribs['class'] : '';

		// Set default dimensions
		$width	= isset($attribs['width']) ? $attribs['width'] : 400;
		$height	= isset($attribs['height']) ? $attribs['height'] : 400;

		// Ignore if in a popup window.
		if ($params && $params->get('popup'))
		{
			return;
		}

		// Ignore if the state is negative (trashed).
		if ($item->state < 0)
		{
			return;
		}

		JHtml::_('behavior.tooltip');

		$url	= 'index.php?option=com_wissensmatrix&task=z'.$attribs['type'].'.edit&tmpl=component&a_id='.$id.'&return='.base64_encode($uri);
		if (isset($attribs['mit_id']))
		{
			$url	.= '&mit_id='.$attribs['mit_id'];
		}

		$icon	= $item->state ? 'edit' : 'eye-close';
		$text	= '<i class="hasTip icon-'.$icon.' tip" title="'.JText::_('COM_WISSENSMATRIX_EDIT_ITEM').'"></i>';
		$text	.= ' '.JText::_('JGLOBAL_EDIT');

		$output = JHtml::_('link', JRoute::_($url), $text, array('class' => 'modal '.$class, 'rel' => "{handler: 'iframe', size: {x: $width, y: $height}}"));

		return $output;
	}

	public static function deletez($item, $params, $attribs = array())
	{
		$uri	= JURI::getInstance();

		$id = ($attribs['type'] == 'wbi') ? $item->zwbi_id : $item->fwig_id;
		$class	= isset($attribs['class']) ? ' '.$attribs['class'] : '';

		// Ignore if in a popup window.
		if ($params && $params->get('popup'))
		{
			return;
		}

		JHtml::_('behavior.tooltip');

		$session	= JFactory::getSession();
		$url	= 'index.php?option=com_wissensmatrix&task=z'.$attribs['type'].'.delete&mit_id='.$item->mit_id.'&a_id='.$id.'&return='.base64_encode($uri).'&'.$session->getName().'='.$session->getId().'&'.JSession::getFormToken().'=1';

		$text	= '<i class="hasTip icon-remove tip" title="'.JText::_('COM_WISSENSMATRIX_DELETE_ITEM').'"></i>';
		$text	.= ' '.JText::_('JACTION_DELETE');

		$output = JHtml::_('link', JRoute::_($url), $text, array('class' => $class));

		return $output;
	}

	public static function print_popup($item, $params, $attribs = array())
	{
		$url  = WissensmatrixHelperRoute::getWorkerRoute($item->slug, $item->catid);
		$url .= '&tmpl=component&print=1&layout=default';

		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

		$text = '<i class="icon-print"></i> '.JText::_('JGLOBAL_PRINT');

		$attribs['title']	= JText::_('JGLOBAL_PRINT');
		$attribs['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";
		$attribs['rel']		= 'nofollow';

		return JHtml::_('link', JRoute::_($url), $text, $attribs);
	}

	public static function print_screen($item, $params, $attribs = array())
	{
		$text = $text = '<i class="icon-print"></i> '.JText::_('JGLOBAL_PRINT');

		return '<a href="#" onclick="window.print();return false;">'.$text.'</a>';
	}
}