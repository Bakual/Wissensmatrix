<?php
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
JHtml::stylesheet('com_wissensmatrix/wissensmatrix.css', '', true);

// AmChart Scriptfiles
JHtml::script('com_wissensmatrix/amcharts/amcharts.js', '', true);
JHtml::script('com_wissensmatrix/amcharts/radar.js', '', true);
$js	= 'AmCharts.ready(function () {'
		.'var chart = new AmCharts.AmRadarChart();'

		.'var chartData = ['
			.'{'
				.'"country": "Czech Republic",'
				.'"litres": 156.9'
			.'},'
			.'{'
				.'"country": "Ireland",'
				.'"litres": 131.1'
			.'},'
			.'{'
				.'"country": "Germany",'
				.'"litres": 115.8'
			.'},'
			.'{'
				.'"country": "Australia",'
				.'"litres": 109.9'
			.'},'
			.'{'
				.'"country": "Austria",'
				.'"litres": 108.3'
			.'},'
			.'{'
				.'"country": "UK",'
				.'"litres": 99'
			.'}'
		.'];'

		.'chart.dataProvider = chartData;'
		.'chart.categoryField = "country";'
		.'chart.startDuration = 2;'

		.'var valueAxis = new AmCharts.ValueAxis();'
		.'valueAxis.axisAlpha = 0.15;'
		.'valueAxis.minimum = 0;'
		.'valueAxis.dashLength = 3;'
		.'valueAxis.axisTitleOffset = 20;'
		.'valueAxis.gridCount = 5;'
		.'chart.addValueAxis(valueAxis);'

		.'var graph = new AmCharts.AmGraph();'
		.'graph.valueField = "litres";'
		.'graph.bullet = "round";'
		.'graph.balloonText = "[[value]] litres of beer per year";'
		.'chart.addGraph(graph);'

		.'chart.write("chartdiv");'
	.'});';
JFactory::getDocument()->addScriptDeclaration($js);

$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
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
			<?php if (!count($this->items)) : ?>
				<div class="no_entries alert alert-error"><?php echo JText::sprintf('COM_WISSENSMATRIX_NO_ENTRIES', JText::_('COM_WISSENSMATRIX_FWIGS')); ?></div>
			<?php else : ?>
				<h3><?php echo JText::_('COM_WISSENSMATRIX_SUMMARY').': '.JText::_('COM_WISSENSMATRIX_LEVELS'); ?></h3>
				<div id="chartdiv">
				
				</div>
				<table class="table table-striped table-hover table-condensed">
					<thead>
						<tr>
							<th class="title">
								<?php echo JHTML::_('grid.sort', 'COM_WISSENSMATRIX_FWIG', 'title', $listDirn, $listOrder); ?>
							</th>
							<?php foreach ($this->levels as $level) : 
								if (!$level->value) continue; ?>
								<th colspan="2" class="center">
									<?php echo $level->title; ?><br/>
								</th>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->items as $item) : ?>
							<tr>
								<td>
									<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportfwiglevels&id='.$item->id); ?>">
										<?php echo $item->title; ?>
									</a>
								</td>
								<?php foreach ($this->levels as $key => $level) :
									if (!$level->value) continue;
									$ist	= (isset($this->ist[$key][$item->id])) ? $this->ist[$key][$item->id]->mit_count : 0;
									$soll	= (isset($this->soll[$key][$item->id])) ? $this->soll[$key][$item->id]->mit_count : 0;
									$wert	= ($soll) ? round($ist/$soll*100) : 100;
									$class	= WissensmatrixHelperWissensmatrix::getPercentClass($wert);
									?>
									<td class="cell-right">
										<span class="label label-<?php echo $class; ?>">
											<?php echo ($ist or $soll) ? $wert.'%' : 'n/a'; ?> 
										</span>
									</td>
									<td>
										<span class="text-left label label-<?php echo $class; ?>">
											<?php echo $ist.' / '.$soll; ?>
										</span>
									</td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
						<tr class="info">
							<td><?php echo JText::_('COM_WISSENSMATRIX_TOTAL'); ?></td>
							<?php foreach ($this->levels as $key => $level) :
								if (!$level->value) continue;
								$ist	= $this->ist_total[$key];
								$soll	= $this->soll_total[$key];
								$wert	= ($soll) ? round($ist/$soll*100) : 100;
								$class	= WissensmatrixHelperWissensmatrix::getPercentClass($wert);
								?>
								<td class="cell-right">
									<span class="label label-<?php echo $class; ?>">
										<?php echo ($ist or $soll) ? $wert.'%' : 'n/a'; ?> 
									</span>
								</td>
								<td>
									<span class="text-left label label-<?php echo $class; ?>">
										<?php echo $ist.' / '.$soll; ?>
									</span>
								</td>
							<?php endforeach; ?>
						</tr>
					</tbody>
				</table>
			<?php endif; ?>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<input type="hidden" name="limitstart" value="" />
		</form>
	</div>
</div>