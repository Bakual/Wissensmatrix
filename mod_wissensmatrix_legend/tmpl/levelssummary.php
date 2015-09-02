<?php
defined('_JEXEC') or die;
?>
<div class="wm-legend<?php echo $moduleclass_sfx; ?>">
	<ul class="list-striped list-condensed">
		<li>
			<span
				class="label label-<?php echo WissensmatrixHelperWissensmatrix::getPercentClass(101); ?>">&gt; 100%</span>
		</li>
		<li>
			<span class="label label-<?php echo WissensmatrixHelperWissensmatrix::getPercentClass(100); ?>">100%</span>
		</li>
		<li>
			<span
				class="label label-<?php echo WissensmatrixHelperWissensmatrix::getPercentClass(99); ?>">75 - 100%</span>
		</li>
		<li>
			<span
				class="label label-<?php echo WissensmatrixHelperWissensmatrix::getPercentClass(74); ?>">50 - 75%</span>
		</li>
		<li>
			<span
				class="label label-<?php echo WissensmatrixHelperWissensmatrix::getPercentClass(49); ?>">&lt; 50%</span>
		</li>
	</ul>
</div>