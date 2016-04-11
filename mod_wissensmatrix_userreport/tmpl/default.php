<?php
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
?>
<form action="<?php echo JRoute::_(htmlspecialchars(JUri::getInstance()->toString())); ?>" method="post" id="userreport-form" class="form-inline">
	<div class="userdata">
		<div id="form-userreport-uid" class="control-group">
			<div class="controls">
				<?php if (!$params->get('usetext')) : ?>
					<div class="input-prepend">
						<span class="add-on">
							<span class="icon-user hasTooltip"
								  title="<?php echo JText::_('MOD_WISSENSMATRIX_USERREPORT_USERID_DESC') ?>"></span>
							<label for="moduserreport-uid"
								   class="element-invisible"><?php echo JText::_('MOD_WISSENSMATRIX_USERREPORT_USERID_LABEL'); ?></label>
						</span>
						<input id="moduserreport-uid" type="text" name="uid" class="input-small" tabindex="0" size="18"
							   placeholder="<?php echo JText::_('MOD_WISSENSMATRIX_USERREPORT_PLACEHOLDER_USERNAME') ?>"/>
					</div>
				<?php else: ?>
					<label for="moduserreport-uid" class="hasTooltip"
						   title="<?php echo JText::_('MOD_WISSENSMATRIX_USERREPORT_USERID_DESC') ?>"><?php echo JText::_('MOD_WISSENSMATRIX_USERREPORT_USERID_LABEL') ?></label>
					<input id="moduserreport-uid" type="text" name="uid" class="input-small" tabindex="0" size="18"
						   placeholder="<?php echo JText::_('MOD_WISSENSMATRIX_USERREPORT_PLACEHOLDER_USERNAME') ?>"/>
				<?php endif; ?>
			</div>
		</div>
		<div id="form-login-submit" class="control-group">
			<div class="controls">
				<button type="submit" tabindex="0" name="Submit"
						class="btn btn-primary"><?php echo JText::_('MOD_WISSENSMATRIX_USERREPORT_REQUEST') ?></button>
			</div>
		</div>
		<input type="hidden" name="option" value="com_wissensmatrix"/>
		<input type="hidden" name="task" value="userreport.request"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>