<?php
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

// Create shortcut to parameters.
$params = $this->state->get('params');
?>

<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'zwbi.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
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

	<form action="<?php echo JRoute::_('index.php?option=com_wissensmatrix&a_id=' . (int) $this->item->id); ?>"
		  method="post" name="adminForm" id="adminForm" class="form-validate form-vertical">
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('zwbi.save')">
					<i class="icon-ok"></i> <?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="Joomla.submitbutton('zwbi.cancel')">
					<i class="icon-cancel"></i> <?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
		</div>
		<fieldset>
			<div class="tab-content">
				<div id="details">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('wbi_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('wbi_id'); ?>
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('date'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('date'); ?>
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('status_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('status_id'); ?>
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('bemerkung'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('bemerkung'); ?>
						</div>
					</div>
				</div>
				<div class="well">
					<?php echo JText::_('COM_WISSENSMATRIX_WBI_NOT_PRESENT'); ?>
					<a href="mailto:Thomas Hunziker &lt;thomi.hunziker@sbb.ch&gt;?cc=Urs Sattler &lt;urs.sattler@sbb.ch&gt;&amp;subject=Wissensmatrix%20-%20Neuen%20Kurs%20erfassen">
						<i class="icon-mail"> </i>
					</a>
				</div>

				<?php echo $this->form->getInput('id'); ?>
				<?php echo $this->form->getInput('mit_id'); ?>
				<?php echo $this->form->getInput('worker_catid'); ?>
				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="return" value="<?php echo $this->return_page; ?>"/>
			</div>
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
	</form>
</div>
