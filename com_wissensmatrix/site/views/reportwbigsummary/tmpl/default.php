<?php
defined('_JEXEC') or die;

JHTML::addIncludePath(JPATH_COMPONENT . '/helpers');

$listOrder = $this->w_state->get('list.ordering');
$listDirn  = $this->w_state->get('list.direction');
?>
<div
	class="category-list<?php echo $this->pageclass_sfx; ?> wm-reportwbigsummary-container<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	<?php endif;
	if ($this->params->get('page_subheading')) : ?>
		<h2>
			<?php echo $this->escape($this->params->get('page_subheading')); ?>
		</h2>
	<?php endif; ?>
	<div class="cat-items">
		<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" id="adminForm"
			  name="adminForm">
			<?php if ($this->params->get('filter_field') or $this->params->get('show_pagination_limit')) : ?>
				<div id="filter-bar" class="filters btn-toolbar">
					<?php if ($this->params->get('filter_field')) : ?>
						<div class="btn-group filter-select input-append">
							<select name="teamid" id="filter_teamid" class="input-xlarge" onchange="this.form.submit()">
								<option value="0"><?php echo JText::_('COM_WISSENSMATRIX_SELECT_TEAM'); ?></option>
								<?php $config = array('filter.published' => array(0, 1), 'filter.access' => true);
								echo JHtml::_('select.options', JHtml::_('wissensmatrixcategory.options', 'com_wissensmatrix', $config), 'value', 'text', $this->state->get('team.id', 0)); ?>
							</select>
							<?php if ($this->parent) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportwbigsummary&teamid=' . $this->parent->id); ?>"
								   class="btn addon"
								   title="<?php JText::printf('COM_WISSENSMATRIX_GET_PARENT_TEAM', $this->parent->title); ?>"
								   rel="tooltip"><i class="icon-arrow-up"></i></a>
							<?php endif; ?>
						</div>
						<div class="btn-group filter-select">
							<select name="zwbistate" id="filter_zwbistate" class="input-medium"
									onchange="this.form.submit()">
								<option value="0"><?php echo JText::_('COM_WISSENSMATRIX_SELECT_STATE'); ?></option>
								<?php $options = array(1 => JText::_('COM_WISSENSMATRIX_ZWBI_STATE_1'), 2 => JText::_('COM_WISSENSMATRIX_ZWBI_STATE_2'), 3 => JText::_('COM_WISSENSMATRIX_ZWBI_STATE_3'));
								echo JHtmlSelect::options($options, 'value', 'text', $this->w_state->get('filter.zwbistate', 0)); ?>
							</select>
						</div>
						<div class="btn-group filter-checkbox">
							<select name="wbirefresh" id="filter_wbirefresh" class="input-medium"
									onchange="this.form.submit()">
								<?php $options = array(0 => JText::_('JALL'), 1 => JText::_('COM_WISSENSMATRIX_REFRESH'));
								echo JHtmlSelect::options($options, 'value', 'text', $this->wbis_state->get('filter.wbirefresh', 0)); ?>
							</select>
						</div>
					<?php endif;
					if ($this->params->get('show_pagination_limit')) : ?>
						<div class="btn-group pull-right">
							<label class="element-invisible">
								<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
							</label>
							<?php echo $this->pagination->getLimitBox(); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="clearfix"></div>
			<h3><?php echo JText::_('COM_WISSENSMATRIX_SUMMARY') . ': ' . JText::_('COM_WISSENSMATRIX_WBIGS'); ?>
				<button type="button" data-toggle="collapse" data-target=".collapse" class="btn btn-mini pull-right">
					<span class="icon-plus"></span>
				</button>
			</h3>
			<?php if (!count($this->items)) : ?>
				<div
					class="no_entries alert alert-error"><?php echo JText::sprintf('COM_WISSENSMATRIX_NO_ENTRIES', JText::_('COM_WISSENSMATRIX_WBIGS')); ?></div>
			<?php else : ?>
				<?php foreach ($this->items as $item) :
					$this->wbis_state->set('wbig.id', $item->id);
					$wbis  = $this->wbismodel->getItems();
					$count = count($wbis);
					?>
					<h3 class="page-header">
						<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportwbigteam&id=' . $item->id); ?>">
							<?php echo $this->escape($item->title); ?>
						</a>
						<?php if ($count) : ?>
							<span class="badge badge-info"
								  title="<?php echo JText::_('COM_WISSENSMATRIX_NUM_ITEMS'); ?>" rel="tooltip">
								<?php echo $count; ?>
							</span>
							<button type="button" data-toggle="collapse" data-target="#wbig<?php echo $item->id; ?>"
									class="btn btn-mini pull-right">
								<span class="icon-plus"></span>
							</button>
						<?php endif; ?>
					</h3>
					<div id="wbig<?php echo $item->id; ?>" class="collapse">
						<?php if (!$count) : ?>
							<div
								class="no_entries alert alert-error"><?php echo JText::sprintf('COM_WISSENSMATRIX_NO_ENTRIES', JText::_('COM_WISSENSMATRIX_WBIS')); ?></div>
						<?php else : ?>
							<?php foreach ($wbis as $wbi) : ?>
								<h4 class="page-header">
									<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportwbiteam&id=' . $wbi->id); ?>">
										<?php echo JText::_('COM_WISSENSMATRIX_WBI') . ': ' . $wbi->title; ?>
									</a>
									<span class="badge" title="<?php echo JText::_('COM_WISSENSMATRIX_NUM_ITEMS'); ?>"
										  rel="tooltip"><?php echo $wbi->mit_count; ?></span>
									<?php if ($wbi->refresh) : ?>
										<span
											class="label label-small pull-right"><?php echo JText::sprintf('COM_WISSENSMATRIX_REFRESH_TEXT', $wbi->refresh); ?></span>
									<?php endif; ?>
								</h4>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			<?php endif;
			if ($this->params->get('show_pagination') and ($this->pagination->get('pages.total') > 1)) : ?>
				<div class="pagination">
					<?php if ($this->params->get('show_pagination_results', 1)) : ?>
						<p class="counter pull-right">
							<?php echo $this->pagination->getPagesCounter(); ?>
						</p>
					<?php endif;
					echo $this->pagination->getPagesLinks(); ?>
				</div>
			<?php endif; ?>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
			<input type="hidden" name="limitstart" value=""/>
		</form>
	</div>
</div>