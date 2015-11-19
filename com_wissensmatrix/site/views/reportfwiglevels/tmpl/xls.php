<?php
defined('_JEXEC') or die;

JHTML::addIncludePath(JPATH_COMPONENT . '/helpers');

// loading PHPExcel (http://www.codeplex.com/PHPExcel)
require_once JPATH_COMPONENT . '/libraries/PHPExcel.php';

$xls = new PHPExcel();

// Set default font
$xls->getDefaultStyle()->getFont()->setName('Arial');
$xls->getDefaultStyle()->getFont()->setSize(10);

$i = '0';
foreach ($this->items as $item) :
	// Create and set an active sheet
	if ($i) :
		$xls->createSheet();
	endif;
	$xls->setActiveSheetIndex($i);

	// Sanitize and shorten $item->title so it can be used as sheet title (max 31 chars allowed)
	$search = array(':', '\\', '/', '?', '*', '[', ']');
	$title  = str_replace($search, '_', $item->title);
	if (strlen($title) > 31) :
		$title = substr($title, 0, 28) . '...';
	endif;
	$xls->getActiveSheet()->setTitle($title);

	// Format Cells
	$xls->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
	$xls->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	$column = ord('B');
	foreach ($this->levels as $level) :
		if (!$level->value) :
			continue;
		endif;
		$column++;
		$xls->getActiveSheet()->getColumnDimension(chr($column))->setAutoSize(true);
		$column++;
		$xls->getActiveSheet()->getColumnDimension(chr($column))->setAutoSize(true);
	endforeach;
	$xls->getActiveSheet()->getStyle('A1:' . chr($column) . '1')->getFont()->setBold(true);

	// Adding Header
	$xls->getActiveSheet()->SetCellValue('A1', JText::_('COM_WISSENSMATRIX_TEAM'));
	$xls->getActiveSheet()->SetCellValue('B1', JText::_('COM_WISSENSMATRIX_WORKER'));
	$column = ord('B');
	foreach ($this->levels as $level) :
		if (!$level->value) :
			continue;
		endif;
		$column++;
		$xls->getActiveSheet()->SetCellValue(chr($column) . '1', $level->title . ' ' . JText::_('COM_WISSENSMATRIX_IST'));
		$column++;
		$xls->getActiveSheet()->SetCellValue(chr($column) . '1', $level->title . ' ' . JText::_('COM_WISSENSMATRIX_SOLL'));
	endforeach;

	// Adding Data
	$j = 1;
	foreach ($this->teams as $team) :
		$j++;
		$xls->getActiveSheet()->SetCellValue('A' . $j, $team->title);
		$xls->getActiveSheet()->SetCellValue('B' . $j, $team->numitems);
		$column = ord('B');
		foreach ($this->levels as $level) :
			if (!$level->value) :
				continue;
			endif;
			$ist  = $this->model->getWorkerCount($item->id, $team->id, $level->value, true);
			$soll = $this->model->getWorkerCount($item->id, $team->id, $level->value, false);
			$column++;
			$xls->getActiveSheet()->SetCellValue(chr($column) . $j, $ist);
			$column++;
			$xls->getActiveSheet()->SetCellValue(chr($column) . $j, $soll);
		endforeach;
	endforeach;

	// Calculate Totals
	$j++;
	$xls->getActiveSheet()->setCellValue('A' . $j, JText::_('COM_WISSENSMATRIX_TOTAL'));
	$xls->getActiveSheet()->setCellValue('B' . $j, '=SUM(B2:B' . ($j - 1) . ')');
	$column = ord('B');
	foreach ($this->levels as $level) :
		if (!$level->value) :
			continue;
		endif;
		$column++;
		$c = chr($column);
		$xls->getActiveSheet()->SetCellValue($c . $j, '=SUM(' . $c . '2:' . $c . ($j - 1) . ')');
		$column++;
		$c = chr($column);
		$xls->getActiveSheet()->SetCellValue($c . $j, '=SUM(' . $c . '2:' . $c . ($j - 1) . ')');
	endforeach;

	$i++;
endforeach;

// Add Footer (Totals)

$xls->setActiveSheetIndex(0);

// generate File
header("200 HTTP/1.0 OK");
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . JText::_('COM_WISSENSMATRIX_FWIG') . '.xlsx"');

$xlsWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
$xlsWriter->save('php://output');
