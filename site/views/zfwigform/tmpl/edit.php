<?php
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

// Create shortcut to parameters.
$params = $this->state->get('params');

// Check if add or edit
$id	= (JFactory::getApplication()->input->get('reload', false, 'bool')) ? 0 : $this->item->fwig_id;
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'zfwig.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			Joomla.submitform(task);
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
		}
	}
</script>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
	<?php if ($params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($params->get('page_heading')); ?>
		</h1>
	</div>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_wissensmatrix&a_id='.(int)$id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-vertical">
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('zfwig.save')">
					<i class="icon-ok"></i> <?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="Joomla.submitbutton('zfwig.cancel')">
					<i class="icon-cancel"></i> <?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
		</div>
		<fieldset>
			<div class="tab-content">
				<div id="details">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('fwig_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('fwig_id'); ?>
						</div>
					</div>
				</div>

				<div id="fwis">
					<table class="table table-striped table-hover">
						<tr>
							<th><?php echo JText::_('COM_WISSENSMATRIX_FWI'); ?></th>
							<th><?php echo JText::_('COM_WISSENSMATRIX_IST'); ?></th>
							<th><?php echo JText::_('COM_WISSENSMATRIX_SOLL'); ?></th>
							<th><?php echo JText::_('COM_WISSENSMATRIX_FIELD_TEMPLATE_LABEL'); ?></th>
						</tr>
						<?php foreach ($this->fwis as $fwi):
							$istsoll	= $this->fwi_model->getIstSoll($fwi->id, $this->item->mit_id); ?>
							<tr>
								<td><?php echo $this->escape($fwi->title); ?></td>
								<td><?php echo JHtml::_('select.genericlist', $this->levels, 'jform[fwis]['.$fwi->id.'][ist]', 'class="input-small"', 'id', 'title', $istsoll['ist_id']); ?></td>
								<td><?php echo JHtml::_('select.genericlist', $this->levels, 'jform[fwis]['.$fwi->id.'][soll]', 'class="input-small"', 'id', 'title', $istsoll['soll_id']); ?></td>
								<td><?php echo $istsoll['template']; ?></td>
							</tr>
						<?php endforeach; ?>
				</div>

				<?php echo $this->form->getInput('mit_id'); ?>
				<?php echo $this->form->getInput('worker_catid'); ?>
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
			</div>
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
	</form>
</div>
