<?php
defined('_JEXEC') or die;
?>
<div class="wm-legend<?php echo $moduleclass_sfx; ?>">
	<h4><?php echo JText::_('MOD_WISSENSMATRIX_LEGEND_SOLL'); ?> - <?php echo JText::_('MOD_WISSENSMATRIX_LEGEND_IST'); ?> = &Delta;</h4>
	<ul class="list-striped list-condensed">
		<li>
			<span class="label label-<?php echo WissensmatrixHelperWissensmatrix::getDiffClass(2, 1); ?>">&Delta; &lt; 0</span>
		</li>
		<li>
			<span class="label label-<?php echo WissensmatrixHelperWissensmatrix::getDiffClass(1, 1); ?>">&Delta; = 0</span>
		</li>
		<li>
			<span class="label label-<?php echo WissensmatrixHelperWissensmatrix::getDiffClass(1, 2); ?>">&Delta; = 1</span>
		</li>
		<li>
			<span class="label label-<?php echo WissensmatrixHelperWissensmatrix::getDiffClass(1, 3); ?>">&Delta; = 2</span>
		</li>
		<li>
			<span class="label label-<?php echo WissensmatrixHelperWissensmatrix::getDiffClass(1, 4); ?>">&Delta; &gt;= 3</span>
		</li>
	</ul>
</div>