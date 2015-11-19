<?php
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
JHtml::stylesheet('com_wissensmatrix/wissensmatrix.css', '', true);

$user      = JFactory::getUser();
$canView   = $user->authorise('wissensmatrix.view.worker', 'com_wissensmatrix');
$listOrder = $this->w_state->get('list.ordering');
$listDirn  = $this->w_state->get('list.direction');
?>
<div
	class="category-list<?php echo $this->pageclass_sfx; ?> wm-reportresponsiblesfwi-container<?php echo $this->pageclass_sfx; ?>">
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
								<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportresponsiblesfwi&id=' . $this->item->id . '&teamid=' . $this->parent->id); ?>"
								   class="btn addon"
								   title="<?php JText::printf('COM_WISSENSMATRIX_GET_PARENT_TEAM', $this->parent->title); ?>"
								   rel="tooltip"><i class="icon-arrow-up"></i></a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="clearfix"></div>
			<h3>
				<?php echo JText::_('COM_WISSENSMATRIX_FWI') . ': ' . $this->item->title; ?>
				<small><?php echo JText::_('COM_WISSENSMATRIX_FWIG'); ?>: <?php echo $this->item->fwig_title; ?>
				</small>
			</h3>
			<?php if (!count($this->workers)) : ?>
				<div
					class="no_entries alert alert-error"><?php echo JText::sprintf('COM_WISSENSMATRIX_NO_ENTRIES', JText::_('COM_WISSENSMATRIX_WORKERS')); ?></div>
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
							<?php echo JText::_('COM_WISSENSMATRIX_RESPONSIBILITY'); ?>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($this->workers as $worker) : ?>
						<?php $istsoll = $this->model->getIstSoll($this->item->id, $worker->id); ?>
						<?php if (!$istsoll['responsibility']) continue; ?>
						<tr>
							<td>
								<?php if ($canView or $user->authorise('wissensmatrix.view.worker', 'com_wissensmatrix.category.' . $worker->catid)) : ?>
									<a href="<?php echo JRoute::_(WissensmatrixHelperRoute::getWorkerRoute($worker->slug)); ?>">
										<?php echo $worker->vorname . ' ' . $worker->name; ?>
									</a>
								<?php else :
									echo $worker->vorname . ' ' . $worker->name;
								endif; ?>
							</td>
							<td>
								<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportresponsiblesfwi&id=' . $this->item->id . '&teamid=' . $worker->catid); ?>"><?php echo $worker->category_title; ?></a>
							</td>
							<td class="center">
								<?php echo JText::_('COM_WISSENSMATRIX_RESPONSIBILITY_' . $istsoll['responsibility']); ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
			<input type="hidden" name="limitstart" value=""/>
		</form>
	</div>
</div>