<?php
defined('_JEXEC') or die;

JHTML::addIncludePath(JPATH_COMPONENT.'/helpers');

$listOrder	= $this->w_state->get('list.ordering');
$listDirn	= $this->w_state->get('list.direction');
?>
<div class="category-list<?php echo $this->pageclass_sfx;?> wm-reportfwiglevels-container<?php echo $this->pageclass_sfx; ?>">
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
						<div class="filter-search btn-group input-append pull-left">
							<label class="filter-search-lbl element-invisible" for="filter-search">
								<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
								<?php echo JText::_('JGLOBAL_FILTER_LABEL').'&#160;'; ?>
							</label>
							<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="input-medium" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_WISSENSMATRIX_FILTER_SEARCH_DESC'); ?>" placeholder="<?php echo JText::_('COM_WISSENSMATRIX_FILTER_SEARCH_DESC'); ?>" />
							<button class="btn tip hidden-phone hidden-tablet" type="button" onclick="clear_all();this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
						</div>
						<div class="btn-group filter-select input-append">
							<select name="teamid" id="filter_teamid" class="input-xlarge" onchange="this.form.submit()">
								<option value="0"><?php echo JText::_('COM_WISSENSMATRIX_SELECT_TEAM'); ?></option>
								<?php $config = array('filter.published' => array(0, 1), 'filter.access' => true);
								echo JHtml::_('select.options', JHtml::_('wissensmatrixcategory.options', 'com_wissensmatrix', $config), 'value', 'text', $this->state->get('team.id', 0)); ?>
							</select>
							<a href="<?php echo JRoute::_('index.php?view=reportfwiglevels&id='.$this->items[0]->fwig_id.'&teamid='.$this->parent->id); ?>" class="btn addon" title="<?php JText::printf('COM_WISSENSMATRIX_GET_PARENT_TEAM', $this->parent->title); ?>"><i class="icon-arrow-up"></i></a>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="clearfix"></div>
			<?php if (!count($this->items)) : ?>
				<div class="no_entries alert alert-error"><?php echo JText::sprintf('COM_WISSENSMATRIX_NO_ENTRIES', JText::_('COM_WISSENSMATRIX_FWIGS')); ?></div>
			<?php else : ?>
				<h3><?php echo JText::_('COM_WISSENSMATRIX_FWIG').': '.$this->items[0]->fwig_title; ?></h3>
				<?php foreach ($this->items as $item) : ?>
					<a href="<?php echo JRoute::_('index.php?view=reportfwiteam&id='.$item->id); ?>">
						<h4><?php echo JText::_('COM_WISSENSMATRIX_FWI').': '.$item->title; ?></h4>
					</a>
					<table class="table table-striped table-hover table-condensed">
						<thead>
							<tr>
								<th class="title">
									<?php echo JHTML::_('grid.sort', 'COM_WISSENSMATRIX_TEAM', 'category_title', $listDirn, $listOrder); ?>
								</th>
								<th><?php echo JText::_('COM_WISSENSMATRIX_WORKERS'); ?></th>
								<?php foreach ($this->levels as $level) :
									if (!$level->value) :
										continue;
									endif; ?>
									<th>
										<?php echo $level->title; ?><br/>
									</th>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tbody>
							<?php $tooltip = JText::_('COM_WISSENSMATRIX_IST').' / '.JText::_('COM_WISSENSMATRIX_SOLL'); ?>
							<?php foreach ($this->teams as $team) : ?>
								<tr>
									<td>
										<a href="<?php echo JRoute::_('index.php?view=reportfwiglevels&id='.$this->items[0]->fwig_id.'&teamid='.$team->id); ?>">
											<?php echo $team->title; ?>
										</a>
									</td>
									<td>
										<?php echo $team->numitems; ?>
									</td>
									<?php foreach ($this->levels as $level) :
										if (!$level->value) :
											continue;
										endif;
										$ist = $this->model->getWorkerCount($item->id, $team->id, $level->value, true);
										$soll = $this->model->getWorkerCount($item->id, $team->id, $level->value, false);
										$class = WissensmatrixHelperWissensmatrix::getDiffClass($ist, $soll); ?>
										<td>
											<span class="label label-<?php echo $class; ?>" rel="tooltip" title="<?php echo $tooltip; ?>"><?php echo $ist.' / '.$soll; ?></span>
										</td>
									<?php endforeach; ?>
									<td>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
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