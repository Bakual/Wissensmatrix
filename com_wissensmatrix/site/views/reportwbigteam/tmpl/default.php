<?php
defined('_JEXEC') or die;

JHTML::addIncludePath(JPATH_COMPONENT.'/helpers');

$listOrder	= $this->w_state->get('list.ordering');
$listDirn	= $this->w_state->get('list.direction');
?>
<div class="category-list<?php echo $this->pageclass_sfx;?> wm-reportwbigteam-container<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	<?php endif;
	if ($this->params->get('page_subheading')) : ?>
		<h2>
			<?php echo $this->escape($this->params->get('page_subheading')); ?>
		</h2>
	<?php endif; ?>
	<div class="cat-items">
		<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" id="adminForm" name="adminForm">
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
								<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportwbigteam&id='.$this->item->id.'&teamid='.$this->parent->id); ?>" class="btn addon" title="<?php JText::printf('COM_WISSENSMATRIX_GET_PARENT_TEAM', $this->parent->title); ?>" rel="tooltip"><i class="icon-arrow-up"></i></a>
							<?php endif; ?>
						</div>
						<div class="btn-group filter-select">
							<select name="zwbistate" id="filter_zwbistate" class="input-medium" onchange="this.form.submit()">
								<option value="0"><?php echo JText::_('COM_WISSENSMATRIX_SELECT_STATE'); ?></option>
								<?php $options = array(1 => JText::_('COM_WISSENSMATRIX_ZWBI_STATE_1'), 2 => JText::_('COM_WISSENSMATRIX_ZWBI_STATE_2'));
								echo JHtmlSelect::options($options, 'value', 'text', $this->w_state->get('filter.zwbistate', 0)); ?>
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
			<h3><?php echo JText::_('COM_WISSENSMATRIX_WBIG').': '.$this->item->title; ?></h3>
			<?php if (!count($this->items)) : ?>
				<div class="no_entries alert alert-error"><?php echo JText::sprintf('COM_WISSENSMATRIX_NO_ENTRIES', JText::_('COM_WISSENSMATRIX_WBIS')); ?></div>
			<?php else : ?>
				<?php foreach ($this->items as $item) : 
					$this->w_state->set('wbi.id', $item->id);
					$workers = $this->workermodel->getItems(); ?>
					<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportwbiteam&id='.$item->id); ?>">
						<h4><?php echo JText::_('COM_WISSENSMATRIX_WBI').': '.$item->title; ?>
						 <small class="badge"><?php echo $item->mit_count; ?></small></h4>
					</a>
					<?php if ($item->refresh) : ?>
						 <div class="well well-small"><?php echo JText::sprintf('COM_WISSENSMATRIX_REFRESH_TEXT', $item->refresh); ?></div>
					<?php endif; ?>
					<?php if (!count($workers)) : ?>
						<div class="no_entries alert alert-error"><?php echo JText::sprintf('COM_WISSENSMATRIX_NO_ENTRIES', JText::_('COM_WISSENSMATRIX_WORKERS')); ?></div>
					<?php else : ?>
						<table class="table table-striped table-hover table-condensed">
							<thead>
								<tr>
									<th class="title">
										<?php echo JHTML::_('grid.sort', 'COM_WISSENSMATRIX_VORNAME', 'vorname', $listDirn, $listOrder); ?>
										<?php echo JHTML::_('grid.sort', 'COM_WISSENSMATRIX_NACHNAME', 'name', $listDirn, $listOrder); ?>
									</th>
									<th class="hidden-phone">
										<?php echo JHTML::_('grid.sort', 'COM_WISSENSMATRIX_TEAM', 'category_title', $listDirn, $listOrder); ?>
									</th>
									<th class="center">
										<?php echo JHTML::_('grid.sort', 'COM_WISSENSMATRIX_STATE', 'zwbi_status_id', $listDirn, $listOrder); ?>
									</th>
									<th>
										<?php echo JHTML::_('grid.sort', 'JDATE', 'date', $listDirn, $listOrder); ?>
									</th>
									<?php if ($item->refresh) : ?>
										<th>
											<?php echo JHTML::_('grid.sort', 'COM_WISSENSMATRIX_TARGET_DATE', 'zwbi_refresh', $listDirn, $listOrder); ?>
										</th>
									<?php endif; ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($workers as $worker) : ?>
									<tr>
										<td>
											<a href="<?php echo WissensmatrixHelperRoute::getWorkerRoute($worker->slug); ?>"><?php echo $worker->vorname.' '.$worker->name; ?></a>
										</td>
										<td>
											<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportwbigteam&id='.$item->wbig_id.'&teamid='.$worker->catid); ?>"><?php echo $worker->category_title; ?></a>
										</td>
										<td class="center">
											<span class="zwbi-state badge badge-<?php echo ($worker->zwbi_status_id == 2) ? 'success' : 'info'; ?>">
												<?php echo JText::_('COM_WISSENSMATRIX_ZWBI_STATE_'.$worker->zwbi_status_id); ?>
											</span>
										</td>
										<td><?php echo JHtml::date($worker->date, JText::_('DATE_FORMAT_LC4')); ?></td>
										<?php if ($item->refresh) : ?>
											<?php $refresh_up = '';
											if (strtotime($worker->zwbi_refresh) < strtotime(JHtml::date('now', 'Y-m-d'))) :
												$refresh_up = 'class="label label-important" title="'.JText::_('COM_WISSENSMATRIX_REFRESH_UP').'" rel="tooltip"';
											endif; ?>
											<td>
												<span <?php echo $refresh_up; ?>><?php echo JHtml::date($worker->zwbi_refresh, JText::_('DATE_FORMAT_LC4')); ?></span>
											</td>
										<?php endif; ?>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
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
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<input type="hidden" name="limitstart" value="" />
		</form>
	</div>
</div>