<?php
defined('_JEXEC') or die;
?>
<div class="wm-legend<?php echo $moduleclass_sfx; ?>">
	<ul class="list-striped list-condensed">
		<li>
			<span class="label label-success"><?php echo JText::_('COM_WISSENSMATRIX_POTENTIAL'); ?></span>
		</li>
		<li>
			<span class="label label-info"><?php echo JText::_('COM_WISSENSMATRIX_MANKO'); ?> &lt; 33%</span>
		</li>
		<li>
			<span class="label label-warning"><?php echo JText::_('COM_WISSENSMATRIX_MANKO'); ?> &gt; 33%</span>
		</li>
	</ul>
</div>