<?php
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
JHtml::stylesheet('com_wissensmatrix/wissensmatrix.css', '', true);

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
			<?php if ($this->params->get('filter_field')) : ?>
				<div id="filter-bar" class="filters btn-toolbar">
					<?php if ($this->params->get('filter_field')) : ?>
						<div class="filter-search btn-group input-append pull-left">
							<label class="filter-search-lbl element-invisible" for="filter-search">
								<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
								<?php echo JText::_('JGLOBAL_FILTER_LABEL').'&#160;'; ?>
							</label>
							<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="input-medium" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_WISSENSMATRIX_FILTER_SEARCH_DESC'); ?>" placeholder="<?php echo JText::_('COM_WISSENSMATRIX_FILTER_SEARCH_DESC'); ?>" />
							<button class="btn tip hidden-phone hidden-tablet" type="button" onclick="document.id('filter-search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
						</div>
						<div class="btn-group filter-select input-append">
							<select name="teamid" id="filter_teamid" class="input-xlarge" onchange="this.form.submit();">
								<option value="0"><?php echo JText::_('COM_WISSENSMATRIX_SELECT_TEAM'); ?></option>
								<?php $config = array('filter.published' => array(0, 1), 'filter.access' => true);
								echo JHtml::_('select.options', JHtml::_('wissensmatrixcategory.options', 'com_wissensmatrix', $config), 'value', 'text', $this->state->get('team.id', 0)); ?>
							</select>
							<?php if ($this->parent) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportfwigdiff&id='.$this->items[0]->fwig_id.'&teamid='.$this->parent->id); ?>" class="btn addon" title="<?php JText::printf('COM_WISSENSMATRIX_GET_PARENT_TEAM', $this->parent->title); ?>" rel="tooltip"><i class="icon-arrow-up"></i></a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="clearfix"></div>
			<?php if (!count($this->items)) : ?>
				<div class="no_entries alert alert-error"><?php echo JText::sprintf('COM_WISSENSMATRIX_NO_ENTRIES', JText::_('COM_WISSENSMATRIX_FWIGS')); ?></div>
			<?php else : ?>
				<h3><?php echo JText::_('COM_WISSENSMATRIX_FWIG').': '.$this->items[0]->fwig_title; ?></h3>
				<?php foreach ($this->items as $item) :
					$summe['diff']	= 0; 
					$summe['diff1']	= 0; 
					$summe['diff2']	= 0; 
					$summe['diff3']	= 0; 
					$summe['pot']	= 0; 
					$summe['pot1']	= 0; 
					$summe['pot2']	= 0; 
					$summe['pot3']	= 0; ?>
					<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportfwiteam&id='.$item->id); ?>">
						<h4><?php echo JText::_('COM_WISSENSMATRIX_FWI').': '.$item->title; ?></h4>
					</a>
					<table class="table table-striped table-hover table-condensed">
						<thead>
							<tr>
								<th class="title">
									<?php echo JHTML::_('grid.sort', 'COM_WISSENSMATRIX_TEAM', 'category_title', $listDirn, $listOrder); ?>
								</th>
								<th class="text-center"><?php echo JText::_('COM_WISSENSMATRIX_MANKO'); ?></th>
								<th class="text-center diff1">1</th>
								<th class="text-center diff2">2</th>
								<th class="text-center diff3">3</th>
								<th class="text-center"><?php echo JText::_('COM_WISSENSMATRIX_POTENTIAL'); ?></th>
								<th class="text-center pot1">1</th>
								<th class="text-center pot2">2</th>
								<th class="text-center pot3">3</th>
							</tr>
						</thead>
						<tbody>
							<?php 
							$report	= (count($this->teams) > 1) ? 'reportfwigdiff' : 'reportfwigteam';
							foreach ($this->teams as $team) : ?>
								<tr>
									<td>
										<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view='.$report.'&id='.$this->items[0]->fwig_id.'&teamid='.$team->id); ?>">
											<?php echo $team->title; ?>
										</a>
									</td>
									<td class="text-center diff">
										<?php $diff	= $this->model->getDiff($item->id, $team->id);
										$summe['diff']	+= $diff;
										echo $diff; ?>
									</td>
									<td class="text-center diff1">
										<?php $diff1	= $this->model->getDiff($item->id, $team->id, false, 1);
										$summe['diff1']	+= $diff1;
										echo $diff1; ?>
									</td>
									<td class="text-center diff2">
										<?php $diff2	= $this->model->getDiff($item->id, $team->id, false, 2);
										$summe['diff2']	+= $diff2;
										echo $diff2; ?>
									</td>
									<td class="text-center diff3">
										<?php $diff3	= $this->model->getDiff($item->id, $team->id, false, 3);
										$summe['diff3']	+= $diff3;
										echo $diff3; ?>
									</td>
									<td class="text-center pot">
										<?php $pot		= $this->model->getDiff($item->id, $team->id, true);
										$summe['pot']	+= $pot;
										echo $pot; ?>
									</td>
									<td class="text-center pot1">
										<?php $pot1		= $this->model->getDiff($item->id, $team->id, true, 1);
										$summe['pot1']	+= $pot1;
										echo $pot1; ?>
									</td>
									<td class="text-center pot2">
										<?php $pot2	= $this->model->getDiff($item->id, $team->id, true, 2);
										$summe['pot2']	+= $pot2;
										echo $pot2; ?>
									</td>
									<td class="text-center pot3">
										<?php $pot3	= $this->model->getDiff($item->id, $team->id, true, 3);
										$summe['pot3']	+= $pot3;
										echo $pot3; ?>
									</td>
								</tr>
							<?php endforeach; ?>
							<tr class="info">
								<td><?php echo JText::_('COM_WISSENSMATRIX_TOTAL'); ?></td>
								<?php foreach ($summe as $key => $value) : ?>
									<td class="text-center <?php echo $key; ?>"><?php echo $value; ?></td>
								<?php endforeach; ?>
							</tr>
						</tbody>
					</table>
				<?php endforeach;
			endif; ?>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<input type="hidden" name="limitstart" value="" />
		</form>
	</div>
</div>