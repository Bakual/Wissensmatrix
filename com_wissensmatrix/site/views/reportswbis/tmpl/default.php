<?php
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

$user       = JFactory::getUser();
$canEdit    = $user->authorise('core.edit', 'com_wissensmatrix');
$canEditOwn = $user->authorise('core.edit.own', 'com_wissensmatrix');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
?>
<div
	class="category-list<?php echo $this->pageclass_sfx; ?> wm-reportswbigs-container<?php echo $this->pageclass_sfx; ?>">
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
						<div class="filter-search btn-group input-append pull-left">
							<label class="filter-search-lbl element-invisible" for="filter-search">
								<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
								<?php echo JText::_('JGLOBAL_FILTER_LABEL') . '&#160;'; ?>
							</label>
							<input type="text" name="filter-search" id="filter-search"
								   value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
								   class="input-medium" onchange="document.adminForm.submit();"
								   title="<?php echo JText::_('COM_WISSENSMATRIX_FILTER_SEARCH_DESC'); ?>"
								   placeholder="<?php echo JText::_('COM_WISSENSMATRIX_FILTER_SEARCH_DESC'); ?>"/>
							<button class="btn tip hidden-phone hidden-tablet" type="button"
									onclick="document.getElementById('filter-search').value='';this.form.submit();" rel="tooltip"
									title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i>
							</button>
						</div>
						<div class="btn-group filter-select input-append">
							<select name="teamid" id="filter_teamid" class="input-xlarge" onchange="this.form.submit()">
								<option value="0"><?php echo JText::_('COM_WISSENSMATRIX_SELECT_TEAM'); ?></option>
								<?php $config = array('filter.published' => array(0, 1), 'filter.access' => true);
								echo JHtml::_('select.options', JHtml::_('wissensmatrixcategory.options', 'com_wissensmatrix', $config), 'value', 'text', $this->state->get('team.id', 0)); ?>
							</select>
							<?php if ($this->parent) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportswbis&teamid=' . $this->parent->id); ?>"
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
				<div class="btn-group filter-select pull-left">
					<select name="id" id="filter_id" class="input-xlarge" onchange="this.form.submit()">
						<option value="0"><?php echo JText::_('COM_WISSENSMATRIX_FIELD_WBIG_ID_SELECT'); ?></option>
						<?php echo JHtmlSelect::options($this->wbigs, 'id', 'title', $this->state->get('wbig.id')); ?>
					</select>
				</div>
			<?php endif; ?>
			<div class="clearfix"></div>
			<?php if (!count($this->items)) : ?>
				<div
					class="no_entries alert alert-error"><?php echo JText::sprintf('COM_WISSENSMATRIX_NO_ENTRIES', JText::_('COM_WISSENSMATRIX_WBIS')); ?></div>
			<?php else : ?>
				<table class="table table-striped table-hover table-condensed">
					<thead>
					<tr>
						<th class="title">
							<?php echo JHTML::_('grid.sort', 'JGLOBAL_TITLE', 'title', $listDirn, $listOrder); ?>
						</th>
						<th class="reports">
							<?php echo JText::_('COM_WISSENSMATRIX_TEAMS'); ?>
						</th>
					</tr>
					</thead>
					<!-- Begin Data -->
					<tbody>
					<?php foreach ($this->items as $i => $item) : ?>
						<tr class="<?php echo ($item->state) ? '' : 'system-unpublished '; ?>cat-list-row<?php echo $i % 2; ?>">
							<td class="title">
								<span
									title="<?php echo JText::_('COM_WISSENSMATRIX_WBIG') . ': ' . $item->wbig_title; ?>"
									rel="tooltip"><?php echo $item->title; ?></span>
								<?php if (!$item->state) : ?>
									<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
								<?php endif; ?>
							</td>
							<td class="reports">
								<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportwbiteam&id=' . $item->id); ?>"><img
										src="media/com_wissensmatrix/images/black_view.gif"></a>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
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