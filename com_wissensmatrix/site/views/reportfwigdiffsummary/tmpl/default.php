<?php
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
JHtml::stylesheet('com_wissensmatrix/wissensmatrix.css', '', true);

// AmChart Scriptfiles
JHtml::script('com_wissensmatrix/amcharts/amcharts.js', '', true);
JHtml::script('com_wissensmatrix/amcharts/radar.js', '', true);
$js           = 'AmCharts.ready(function () {'
	// Chart
	. 'var chart = new AmCharts.AmRadarChart();'
	. 'chart.dataProvider = chartData;'
	. 'chart.categoryField = "fwig";'
	. 'chart.startDuration = 1;'
	. 'chart.startEffect = ">";'
	// Value Axis
	. 'var valueAxis = new AmCharts.ValueAxis();'
	. 'valueAxis.axisAlpha = 0.15;'
	. 'valueAxis.minimum = 0;'
	. 'valueAxis.fillAlpha = 0.1;'
	. 'valueAxis.fillColor = "#aaaaff";'
	. 'valueAxis.gridType = "circles";'
	. 'valueAxis.showFirstLabel = false;'
	. 'valueAxis.unit = "%";'
	. 'valueAxis.axisTitleOffset = 20;'
	. 'chart.addValueAxis(valueAxis);'
	// Graph Manko
	. 'var graph = new AmCharts.AmGraph();'
	. 'graph.title = "' . JText::_('COM_WISSENSMATRIX_MANKO') . '";'
	. 'graph.valueField = "manko";'
	. 'graph.bullet = "round";'
	. 'graph.balloonText = "[[title]]: [[value]]%";'
	. 'graph.lineColor = "#ff0000";'
	. 'chart.addGraph(graph);'
	// Graph Potential
	. 'var graph = new AmCharts.AmGraph();'
	. 'graph.title = "' . JText::_('COM_WISSENSMATRIX_POTENTIAL') . '";'
	. 'graph.valueField = "potential";'
	. 'graph.bullet = "square";'
	. 'graph.balloonText = "[[title]]: [[value]]%";'
	. 'graph.lineColor = "#00ff00";'
	. 'chart.addGraph(graph);'
	// Legend
	. 'var legend = new AmCharts.AmLegend();'
	. 'legend.align = "center";'
	. 'chart.addLegend(legend);'
	// Finish
	. 'chart.write("chartdiv");'
	. '});';
$chart_values = array();

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
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
			<?php if (!count($this->items)) : ?>
				<div
					class="no_entries alert alert-error"><?php echo JText::sprintf('COM_WISSENSMATRIX_NO_ENTRIES', JText::_('COM_WISSENSMATRIX_FWIGS')); ?></div>
			<?php else : ?>
				<h3><?php echo JText::_('COM_WISSENSMATRIX_SUMMARY') . ': ' . JText::_('COM_WISSENSMATRIX_DIFF'); ?></h3>
				<div id="chartdiv" class="well"></div>
				<table class="table table-striped table-hover table-condensed">
					<thead>
					<tr>
						<th class="title">
							<?php echo JHTML::_('grid.sort', 'COM_WISSENSMATRIX_FWIG', 'title', $listDirn, $listOrder); ?>
						</th>
						<th class="center">
							<?php echo JText::_('COM_WISSENSMATRIX_MANKO'); ?>
						</th>
						<th class="center">
							<?php echo JText::_('COM_WISSENSMATRIX_POTENTIAL'); ?>
						</th>
						<th class="center">
							<?php echo JText::_('COM_WISSENSMATRIX_BASE'); ?>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($this->items as $item) :
						$values               = array('fwig' => $item->title);
						$manko     = isset($this->manko[$item->id]) ? $this->manko[$item->id]->mit_count : 0;
						$potential = isset($this->potential[$item->id]) ? $this->potential[$item->id]->mit_count : 0;
						if (isset($this->workers[$item->id])) :
							$workers     = $this->workers[$item->id]->mit_count;
							$value_manko = round($manko / $workers * 100);
							$value_pot   = round($potential / $workers * 100);
						else :
							$workers     = 0;
							$value_manko = 0;
							$value_pot   = 0;
						endif;
						$values['manko']     = $value_manko;
						$values['potential'] = $value_pot;
						?>
						<tr>
							<td>
								<a href="<?php echo JRoute::_('index.php?option=com_wissensmatrix&view=reportfwigdif&id=' . $item->id); ?>">
									<?php echo $item->title; ?>
								</a>
							</td>
							<td class="center">
									<span class="label label-<?php echo ($value_manko > 33) ? 'warning' : 'info'; ?>">
										<?php echo $value_manko; ?>% (<?php echo $manko; ?>)
									</span>
							</td>
							<td class="center">
									<span class="label label-success">
										<?php echo $value_pot; ?>% (<?php echo $potential; ?>)
									</span>
							</td>
							<td class="center">
									<span>
										<?php echo $workers; ?>
									</span>
							</td>
						</tr>
						<?php $chart_values[] = $values;
					endforeach;
					// Add amChart Data
					$js = 'var chartData = ' . json_encode($chart_values) . ';' . $js;
					JFactory::getDocument()->addScriptDeclaration($js);
					?>
					<tr class="info">
						<td><?php echo JText::_('COM_WISSENSMATRIX_TOTAL'); ?></td>
						<td class="center">
								<span class="label label-<?php echo ($value_manko > 33) ? 'warning' : 'info'; ?>">
									<?php echo round($this->manko_total / $this->workers_total * 100); ?>
									% (<?php echo $this->manko_total; ?>)
								</span>
						</td>
						<td class="center">
								<span class="label label-success">
									<?php echo round($this->potential_total / $this->workers_total * 100); ?>
									% (<?php echo $this->potential_total; ?>)
								</span>
						</td>
						<td class="center">
								<span>
									<?php echo $this->workers_total; ?>
								</span>
						</td>
					</tr>
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