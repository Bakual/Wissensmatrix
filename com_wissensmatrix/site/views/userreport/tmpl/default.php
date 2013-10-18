<?php
defined('_JEXEC') or die;
JHTML::addIncludePath(JPATH_COMPONENT.'/helpers');
JHTML::_('behavior.tooltip');
$listOrder	= $this->state_wbi->get('list.ordering');
$listDirn	= $this->state_wbi->get('list.direction');
$this->document->addScriptDeclaration('jQuery(function() {
		jQuery(\'a[data-toggle="tab"]\').on(\'shown\', function (e) {
			localStorage.setItem(\'lastTab\', jQuery(e.target).attr(\'id\'));
		});
		if (location.hash == \'#fwis\') {
			tab = \'#tab_fwis\';
		} else if (location.hash == \'#wbis\') {
			tab = \'#tab_wbis\';
		} else {
			var lastTab = localStorage.getItem(\'lastTab\');
			if (lastTab) {
				tab = \'#tab_\'+lastTab;
			} else {
				tab = \'#tab_wbis\';
			}
		}
		jQuery(\'#workerTab a[href="\' + tab + \'"]\').tab(\'show\');
	})');
?>
<script type="text/javascript">
	Joomla.orderTable = function() {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>

<div class="item-page<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
	</div>
	<?php endif; ?>
	<div class="page-header">
		<h2><?php echo $this->escape($this->item->title); ?></h2>
	</div>
	<div class="article-info muted">
		<dl class="article-info">
			<dt class="article-info-term"><?php echo JText::_('COM_WISSENSMATRIX_WORKER_INFO'); ?></dt>
			<dd>
				<div class="category-name">
					<i class="icon-users"></i> <?php echo JText::_('COM_WISSENSMATRIX_TEAM'); ?>:
					<?php echo $this->escape($this->item->category_title); ?>
				</div>
			</dd>
			<dd>
				<div class="uid">
					<i class="icon-vcard"></i> <?php echo JText::_('COM_WISSENSMATRIX_FIELD_UID_LABEL'); ?>:
					<?php echo $this->item->uid; ?>
				</div>
			</dd>
			<dd>
				<div class="birthday">
					<i class="icon-calendar"></i> <?php echo JText::_('COM_WISSENSMATRIX_FIELD_GEB_LABEL'); ?>:
					<?php echo JHtml::_('date', $this->item->geb, JText::_('DATE_FORMAT_LC3')); ?>
				</div>
			</dd>
			<dd>
				<div class="entryday">
					<i class="icon-calendar"></i> <?php echo JText::_('COM_WISSENSMATRIX_FIELD_EINTRITT_LABEL'); ?>:
					<?php echo JHtml::_('date', $this->item->eintritt, JText::_('DATE_FORMAT_LC3')); ?>
				</div>
			</dd>
			<dd>
				<div class="template">
					<i class="icon-folder"></i> <?php echo JText::_('COM_WISSENSMATRIX_FIELD_TEMPLATE_LABEL'); ?>:
					<?php echo $this->item->template_title; ?>
				</div>
			</dd>
		</dl>
	</div>
	<ul class="nav nav-tabs" id="workerTab">
		<li><a href="#tab_wbis" id="wbis" data-toggle="tab"><h3><?php echo JText::_('COM_WISSENSMATRIX_WBIS'); ?></h3></a></li>
		<li><a href="#tab_fwis" id="fwis" data-toggle="tab"><h3><?php echo JText::_('COM_WISSENSMATRIX_FWIS'); ?></h3></a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane" id="tab_wbis">
			<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" id="adminForm" name="adminForm" class="form-inline">
				<div class="filters btn-toolbar">
					<div class="btn-group pull-right hidden-phone">
						<label for="directionTable" class="element-invisible"><?php echo JText::_('COM_WISSENSMATRIX_SELECT_DIR');?></label>
						<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
							<option value=""><?php echo JText::_('COM_WISSENSMATRIX_SELECT_DIR');?></option>
							<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('COM_WISSENSMATRIX_ASC');?></option>
							<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('COM_WISSENSMATRIX_DESC');?></option>
						</select>
					</div>
					<div class="btn-group pull-right">
						<label for="sortTable" class="element-invisible"><?php echo JText::_('COM_WISSENSMATRIX_SELECT_ORDER');?></label>
						<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
							<option value=""><?php echo JText::_('COM_WISSENSMATRIX_SELECT_ORDER');?></option>
							<?php echo JHtml::_('select.options', $this->getSortFields(), 'value', 'text', $listOrder);?>
						</select>
					</div>
				</div>
				<a href="http://rsdp.sbb.ch/inetawb/app/public/Welcome_home.action?request_locale=<?php echo substr(JFactory::getLanguage()->getTag(), 0, 2); ?>" target="_new" class="btn btn-info btn-small pull-left">
					<i class="icon-out-2"> </i> <?php echo JText::_('COM_WISSENSMATRIX_INTRANET_WEITERBILDUNG'); ?>
				</a>
				<div class="clearfix"></div>
				<br />
				<ul class="wbi list-striped list-condensed">
					<?php foreach($this->wbis as $i => $item) : ?>
						<li id="wbi<?php echo $i; ?>" class="wbi <?php echo ($item->state) ? '': 'system-unpublished '; ?>cat-list-row<?php echo $i % 2; ?>">
							<span class="zbwi-state badge badge-<?php echo ($item->zwbi_status_id == 2) ? 'success' : 'info'; ?> pull-right" title="<?php echo JText::_('JSTATUS'); ?>">
								<?php echo JText::_('COM_WISSENSMATRIX_ZWBI_STATE_'.$item->zwbi_status_id); ?>
							</span>
							<strong class="title">
								<?php echo $item->title; ?>
							</strong>
							<?php if (!$item->state) : ?>
								<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
							<?php endif; ?>
							<br />
							<small class="date">
								<span class="<?php echo ($item->zwbi_refresh < 0) ? ' label label-important" title="'.JText::_('COM_WISSENSMATRIX_REFRESH_UP') : ''; ?>">
									<?php echo JText::_('JDATE'); ?>: 
									<?php echo JHTML::date($item->date, JText::_('DATE_FORMAT_LC3')); ?>
								</span>
								<?php if ($item->refresh) : ?>
									| <?php JText::printf('COM_WISSENSMATRIX_REFRESH_TEXT', $item->refresh); ?>
								<?php endif; ?>
							</small>
							<?php if ($item->bemerkung) : ?>
								<br />
								<small class="bemerkung">
									<?php echo JText::_('COM_WISSENSMATRIX_FIELD_BEMERKUNG_LABEL'); ?>: 
									<?php echo $this->escape($item->bemerkung); ?>
								</small>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
				<input type="hidden" name="limitstart" value="" />
			</form>
		</div>
		<div class="tab-pane" id="tab_fwis">
			<a href="media/com_wissensmatrix/docs/kriterien_<?php echo substr(JFactory::getLanguage()->getTag(), 0, 1); ?>.pdf" target="_new" class="btn btn-info btn-small pull-left">
				<i class="icon-out-2"> </i> <?php echo JText::_('COM_WISSENSMATRIX_KRITERIEN'); ?>
			</a>
			<div class="clearfix"></div>
			<span class="pull-right">&nbsp;</span>
			<span class="input-small center pull-right badge badge-info"><?php echo JText::_('COM_WISSENSMATRIX_SOLL'); ?></span>
			<span class="input-small center pull-right badge badge-info"><?php echo JText::_('COM_WISSENSMATRIX_IST'); ?></span>
			<div class="fwig">
				<?php $fwig = ''; ?>
				<?php foreach($this->fwis as $i => $item) : ?>
					<?php if ($fwig != $item->fwig_id) : ?>
						<?php if ($fwig) : ?>
							</ul>
						<?php endif; ?>
						<h4>
							<?php echo $item->fwig_title; ?>
						</h4>
						<?php $fwig = $item->fwig_id; ?>
						<ul class="fwig<?php echo $fwig; ?> list-striped list-condensed">
					<?php endif; ?>
					<li id="fwi<?php echo $i; ?>" class="fwi <?php echo ($item->state) ? '': 'system-unpublished '; ?>">
						<span class="input-small center zfwi-soll badge pull-right">
							<?php echo $item->soll_title; ?>
						</span>
						<span class="input-small center zfwi-ist badge badge-<?php echo WissensmatrixHelperWissensmatrix::getDiffClass($item->ist, $item->soll); ?> pull-right">
							<?php echo $item->ist_title; ?>
						</span>
						<strong class="title"><?php echo $item->title; ?></strong>
						<?php if (!$item->state) : ?>
							<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
						<?php endif; ?>
						<br />
					</li>
				<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</div>
