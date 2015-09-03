<?php
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

$user      = JFactory::getUser();
$canView   = $user->authorise('core.view.worker', 'com_wissensmatrix');
$listOrder = $this->w_state->get('list.ordering');
$listDirn  = $this->w_state->get('list.direction');
?>
<div
	class="category-list<?php echo $this->pageclass_sfx; ?> wm-reportfwigteam-container<?php echo $this->pageclass_sfx; ?>">
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
								<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportfwigteam&id=' . $this->items[0]->fwig_id . '&teamid=' . $this->parent->id); ?>"
								   class="btn addon"
								   title="<?php JText::printf('COM_WISSENSMATRIX_GET_PARENT_TEAM', $this->parent->title); ?>"
								   rel="tooltip"><i class="icon-arrow-up"></i></a>
							<?php endif; ?>
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
			<h3>
				<?php echo JText::_('COM_WISSENSMATRIX_FWIG') . ': ' . $this->items[0]->fwig_title; ?>
				<button type="button" data-toggle="collapse" data-target=".collapse" class="btn btn-mini pull-right">
					<span class="icon-plus"></span>
				</button>
			</h3>
			<?php if (!count($this->items)) : ?>
				<div
					class="no_entries alert alert-error"><?php echo JText::sprintf('COM_WISSENSMATRIX_NO_ENTRIES', JText::_('COM_WISSENSMATRIX_FWIS')); ?></div>
			<?php else : ?>
				<?php foreach ($this->items as $item) : ?>
					<h4 class="page-header">
						<button type="button" data-toggle="collapse" data-target="#fwi<?php echo $item->id; ?>"
								class="btn btn-mini pull-right">
							<span class="icon-plus"></span>
						</button>
						<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportfwiteam&id=' . $item->id); ?>">
							<?php echo $item->title; ?>
						</a>
					</h4>
					<div id="fwi<?php echo $item->id; ?>" class="collapse">
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
								<th class="span2 center"><?php echo JText::_('COM_WISSENSMATRIX_IST'); ?></th>
								<th class="span2 center"><?php echo JText::_('COM_WISSENSMATRIX_SOLL'); ?></th>
							</tr>
							</thead>
							<tbody>
							<?php foreach ($this->workers as $worker) :
								$istsoll = $this->model->getIstSoll($item->id, $worker->id);
								if (!$istsoll['ist'] and !$istsoll['soll']) :
									continue;
								endif ?>
								<tr>
									<td>
										<?php if ($canView or $user->authorise('core.view.worker', 'com_wissensmatrix.category.' . $worker->catid)) : ?>
											<a href="<?php echo JRoute::_(WissensmatrixHelperRoute::getWorkerRoute($worker->slug)); ?>">
												<?php echo $worker->vorname . ' ' . $worker->name; ?>
											</a>
										<?php else :
											echo $worker->vorname . ' ' . $worker->name;
										endif; ?>
									</td>
									<td>
										<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportfwigteam&id=' . $item->fwig_id . '&teamid=' . $worker->catid); ?>"><?php echo $worker->category_title; ?></a>
									</td>
									<td class="center">
										<span
											class="btn-block label label-<?php echo WissensmatrixHelperWissensmatrix::getDiffClass($istsoll['ist'], $istsoll['soll']); ?>"><?php echo $istsoll['ist_title']; ?></span>
									</td>
									<td class="center">
										<span class="btn-block label"><?php echo $istsoll['soll_title']; ?></span>
									</td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
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