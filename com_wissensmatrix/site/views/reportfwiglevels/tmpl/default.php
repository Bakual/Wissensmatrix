<?php
defined('_JEXEC') or die;

JHTML::addIncludePath(JPATH_COMPONENT . '/helpers');

$listOrder = $this->w_state->get('list.ordering');
$listDirn  = $this->w_state->get('list.direction');
?>
<div
	class="category-list<?php echo $this->pageclass_sfx; ?> wm-reportfwiglevels-container<?php echo $this->pageclass_sfx; ?>">
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
			<?php if ($this->params->get('filter_field')) : ?>
				<div id="filter-bar" class="filters btn-toolbar">
					<?php if ($this->params->get('filter_field')) : ?>
						<div class="btn-group filter-select input-append">
							<select name="teamid" id="filter_teamid" class="input-xlarge" onchange="this.form.submit()">
								<option value="0"><?php echo JText::_('COM_WISSENSMATRIX_SELECT_TEAM'); ?></option>
								<?php $config = array('filter.published' => array(0, 1), 'filter.access' => true);
								echo JHtml::_('select.options', JHtml::_('wissensmatrixcategory.options', 'com_wissensmatrix', $config), 'value', 'text', $this->state->get('team.id', 0)); ?>
							</select>
							<?php if ($this->parent) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportfwiglevels&id=' . $this->items[0]->fwig_id . '&teamid=' . $this->parent->id); ?>"
								   class="btn addon"
								   title="<?php JText::printf('COM_WISSENSMATRIX_GET_PARENT_TEAM', $this->parent->title); ?>"
								   rel="tooltip"><i class="icon-arrow-up"></i></a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="clearfix"></div>
			<?php if (!count($this->items)) : ?>
				<div
					class="no_entries alert alert-error"><?php echo JText::sprintf('COM_WISSENSMATRIX_NO_ENTRIES', JText::_('COM_WISSENSMATRIX_FWIGS')); ?></div>
			<?php else : ?>
				<h3><?php echo JText::_('COM_WISSENSMATRIX_FWIG') . ': ' . $this->items[0]->fwig_title; ?></h3>
				<?php foreach ($this->items as $item) :
					$summe_worker = 0;
					foreach ($this->levels as $level) :
						if (!$level->value) :
							continue;
						endif;
						$summe[$level->value]['ist']  = 0;
						$summe[$level->value]['soll'] = 0;
					endforeach; ?>
					<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportfwiteam&id=' . $item->id); ?>">
						<h4><?php echo $item->title; ?></h4>
					</a>
					<table class="table table-striped table-hover table-condensed">
						<thead>
						<tr>
							<th class="title">
								<?php echo JHTML::_('grid.sort', 'COM_WISSENSMATRIX_TEAM', 'category_title', $listDirn, $listOrder); ?>
							</th>
							<th class="center"><?php echo JText::_('COM_WISSENSMATRIX_WORKERS'); ?></th>
							<?php foreach ($this->levels as $level) :
								if (!$level->value) :
									continue;
								endif; ?>
								<th class="center">
									<?php echo $level->title; ?><br/>
								</th>
							<?php endforeach; ?>
						</tr>
						</thead>
						<tbody>
						<?php $tooltip = JText::_('COM_WISSENSMATRIX_IST') . ' / ' . JText::_('COM_WISSENSMATRIX_SOLL');
						$report        = (count($this->teams) > 1) ? 'reportfwiglevels' : 'reportfwigteam';
						foreach ($this->teams as $team) : ?>
							<tr>
								<td>
									<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=' . $report . '&id=' . $this->items[0]->fwig_id . '&teamid=' . $team->id); ?>">
										<?php echo $team->title; ?>
									</a>
								</td>
								<td class="center">
									<?php echo $team->numitems;
									$summe_worker += $team->numitems; ?>
								</td>
								<?php foreach ($this->levels as $level) :
									if (!$level->value) :
										continue;
									endif;
									$ist = $this->model->getWorkerCount($item->id, $team->id, $level->value, true);
									$summe[$level->value]['ist'] += $ist;
									$soll = $this->model->getWorkerCount($item->id, $team->id, $level->value, false);
									$summe[$level->value]['soll'] += $soll;
									$class = WissensmatrixHelperWissensmatrix::getDiffClass($ist, $soll); ?>
									<td class="center">
										<span class="label label-<?php echo $class; ?>" rel="tooltip"
											  title="<?php echo $tooltip; ?>"><?php echo $ist . ' / ' . $soll; ?></span>
									</td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
						<tr class="info">
							<td><?php echo JText::_('COM_WISSENSMATRIX_TOTAL'); ?></td>
							<td class="center"><?php echo $summe_worker; ?></td>
							<?php foreach ($summe as $values) :
								$class = WissensmatrixHelperWissensmatrix::getDiffClass($values['ist'], $values['soll']); ?>
								<td class="center">
									<span class="label label-<?php echo $class; ?>" rel="tooltip"
										  title="<?php echo $tooltip; ?>"><?php echo $values['ist'] . ' / ' . $values['soll']; ?></span>
								</td>
							<?php endforeach; ?>
						</tr>
						</tbody>
					</table>
				<?php endforeach;
			endif; ?>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
			<input type="hidden" name="limitstart" value=""/>
		</form>
	</div>
</div>