<?php
defined('_JEXEC') or die;

JHTML::addIncludePath(JPATH_COMPONENT . '/helpers');

$user      = JFactory::getUser();
$canView   = $user->authorise('wissensmatrix.view.worker', 'com_wissensmatrix');
$canEdit   = $user->authorise('wissensmatrix.edit.worker', 'com_wissensmatrix');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
?>
<div class="category-list<?php echo $this->pageclass_sfx; ?> wm-workers-container<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	<?php endif;
	if ($this->params->get('show_category_title', 1) or $this->params->get('page_subheading')) : ?>
		<h2>
			<?php echo $this->escape($this->params->get('page_subheading'));
			if ($this->params->get('show_category_title')) : ?>
				<span class="subheading-category"><?php echo $this->category->title; ?></span>
			<?php endif; ?>
		</h2>
	<?php endif;
	if ($this->params->get('show_description', 1) or $this->params->get('show_description_image', 1)) : ?>
		<div class="category-desc">
			<?php if ($this->params->get('show_description_image') and $this->category->getParams()->get('image')) : ?>
				<img src="<?php echo $this->category->getParams()->get('image'); ?>"/>
			<?php endif;
			if ($this->params->get('show_description') and $this->category->description) :
				echo JHtml::_('content.prepare', $this->category->description, '', 'com_wissensmatrix.category');
			endif; ?>
			<div class="clearfix"></div>
		</div>
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
								<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=workers&teamid=' . $this->parent->id); ?>"
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
			<?php if (!count($this->items)) : ?>
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
					</tr>
					</thead>
					<!-- Begin Data -->
					<tbody>
					<?php foreach ($this->items as $i => $item) : ?>
						<tr class="<?php echo ($item->state) ? '' : 'system-unpublished '; ?>cat-list-row<?php echo $i % 2; ?>">
							<td class="title">
								<?php if ($canView or $user->authorise('wissensmatrix.view.worker', 'com_wissensmatrix.category.' . $item->catid)) : ?>
									<a href="<?php echo JRoute::_(WissensmatrixHelperRoute::getWorkerRoute($item->slug)); ?>">
										<?php echo $item->vorname . ' ' . $item->name; ?>
									</a>
								<?php else :
									echo $item->vorname . ' ' . $item->name;
								endif;
								if ($canEdit or $user->authorise('wissensmatrix.edit.worker', 'com_wissensmatrix.category.' . $item->catid)) : ?>
									<span class="list-edit pull-left">
											<?php echo JHtml::_('icon.edit', $item, $this->params, array('type' => 'worker', 'hide_text' => true)); ?>
										</span>
								<?php endif; ?>
								<?php if (!$item->state) : ?>
									<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
								<?php endif; ?>
							</td>
							<td class="hidden-phone">
								<a href="<?php echo JRoute::_(WissensmatrixHelperRoute::getWorkersRoute($item->catslug)); ?>"><?php echo $item->category_title; ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif;
			if ($user->authorise('core.create', 'com_wissensmatrix') or $user->getAuthorisedCategories('com_wissensmatrix', 'core.create')) :
				echo JHtml::_('icon.create', $this->category, $this->params);
			endif;
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
	<?php if (!empty($this->children[$this->category->id]) and $this->maxLevel != 0) : ?>
		<div class="cat-children">
			<h3><?php echo JTEXT::_('JGLOBAL_SUBCATEGORIES'); ?></h3>
			<?php echo $this->loadTemplate('children'); ?>
		</div>
	<?php endif; ?>
</div>