<?php
/**
 * @package     Wissensmatrix
 * @subpackage  mod_wissensmatrix_birthday
 *
 * @copyright   Copyright (C) 2016 Thomas Hunziker
 * @license     GNU General Public License version 2
 */

defined('_JEXEC') or die;

?>
<div class="wm-birthday<?php echo $moduleclass_sfx; ?>">
	<dl>
	<?php foreach ($list as $item): ?>
		<dt><?php echo $item->vorname . ' ' . $item->name; ?></dt>
		<dd><?php echo JHtml::date($item->geb, JText::_('DATE_FORMAT_LC4')); ?></dd>
	<?php endforeach; ?>
	</dl>
</div>