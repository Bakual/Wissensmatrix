<?php
// no direct access
defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$input = JFactory::getApplication()->input;
?>

<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'fwi.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_wissensmatrix&layout=edit&id=' . (int) $this->item->id); ?>"
	  method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span10 form-horizontal">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('JDETAILS'); ?></a></li>
				<li><a href="#publishing" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></a>
				</li>
				<li><a href="#metadata"
					   data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></a></li>
			</ul>

			<div class="tab-content">
				<!-- Begin Tabs -->
				<div class="tab-pane active" id="general">
					<fieldset class="adminform">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('title_de'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('title_de'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('title_fr'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('title_fr'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('title_it'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('title_it'); ?>
							</div>
						</div>
					</fieldset>
					<div class="row-fluid">
						<div class="span6">
							<h4><?php echo JText::_('JDETAILS'); ?></h4>
							<?php foreach ($this->form->getFieldset('detail') as $field): ?>
								<div class="control-group">
									<?php if (!$field->hidden): ?>
										<div class="control-label">
											<?php echo $field->label; ?>
										</div>
									<?php endif; ?>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="tab-pane" id="publishing">
					<div class="row-fluid">
						<div class="span6">
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('alias'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('alias'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('id'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('id'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('created_by'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('created_by'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('created'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('created'); ?>
								</div>
							</div>
						</div>
						<div class="span6">
							<?php if ($this->item->modified_by) : ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('modified_by'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('modified_by'); ?>
									</div>
								</div>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('modified'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('modified'); ?>
									</div>
								</div>
							<?php endif; ?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('hits'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('hits'); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="metadata">
					<fieldset>
						<?php foreach ($this->form->getFieldset('metadata') as $field): ?>
							<div class="control-group">
								<?php if (!$field->hidden): ?>
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
								<?php endif; ?>
								<div class="controls">
									<?php echo $field->input; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</fieldset>
				</div>
			</div>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>"/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
		<!-- End Content -->
		<!-- Begin Sidebar -->
		<div class="span2">
			<h4><?php echo JText::_('JDETAILS'); ?></h4>
			<hr/>
			<fieldset class="form-vertical">
				<div class="control-group">
					<div class="controls">
						<?php $lang = substr(JFactory::getLanguage()->getTag(), 0, 2);

						echo $this->form->getValue('title_' . $lang); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('state'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('state'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('language'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('language'); ?>
					</div>
				</div>
			</fieldset>
		</div>
		<!-- End Sidebar -->
	</div>
</form>