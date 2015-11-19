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
	$xls->getActiveSheet()->getStyle('A1:I1')->getFont()->setBold(true);
	$xls->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
	$xls->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	$xls->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
	$xls->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
	$xls->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
	$xls->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
	$xls->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
	$xls->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
	$xls->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);

	// Adding Header
	$xls->getActiveSheet()->SetCellValue('A1', JText::_('COM_WISSENSMATRIX_TEAM'));
	$xls->getActiveSheet()->SetCellValue('B1', JText::_('COM_WISSENSMATRIX_MANKO'));
	$xls->getActiveSheet()->SetCellValue('C1', '1');
	$xls->getActiveSheet()->SetCellValue('D1', '2');
	$xls->getActiveSheet()->SetCellValue('E1', '3');
	$xls->getActiveSheet()->SetCellValue('F1', JText::_('COM_WISSENSMATRIX_POTENTIAL'));
	$xls->getActiveSheet()->SetCellValue('G1', '1');
	$xls->getActiveSheet()->SetCellValue('H1', '2');
	$xls->getActiveSheet()->SetCellValue('I1', '3');

	// Adding Data
	$j = 1;
	foreach ($this->teams as $team) :
		$j++;
		$xls->getActiveSheet()->SetCellValue('A' . $j, $team->title);
		$xls->getActiveSheet()->SetCellValue('B' . $j, $this->model->getDiff($item->id, $team->id));
		$xls->getActiveSheet()->SetCellValue('C' . $j, $this->model->getDiff($item->id, $team->id, false, 1));
		$xls->getActiveSheet()->SetCellValue('D' . $j, $this->model->getDiff($item->id, $team->id, false, 2));
		$xls->getActiveSheet()->SetCellValue('E' . $j, $this->model->getDiff($item->id, $team->id, false, 3));
		$xls->getActiveSheet()->SetCellValue('F' . $j, $this->model->getDiff($item->id, $team->id, true));
		$xls->getActiveSheet()->SetCellValue('G' . $j, $this->model->getDiff($item->id, $team->id, true, 1));
		$xls->getActiveSheet()->SetCellValue('H' . $j, $this->model->getDiff($item->id, $team->id, true, 2));
		$xls->getActiveSheet()->SetCellValue('I' . $j, $this->model->getDiff($item->id, $team->id, true, 3));
	endforeach;

	// Calculate Totals
	$j++;
	$xls->getActiveSheet()->setCellValue('A' . $j, JText::_('COM_WISSENSMATRIX_TOTAL'));
	$xls->getActiveSheet()->setCellValue('B' . $j, '=SUM(B2:B' . ($j - 1) . ')');
	$xls->getActiveSheet()->setCellValue('C' . $j, '=SUM(C2:C' . ($j - 1) . ')');
	$xls->getActiveSheet()->setCellValue('D' . $j, '=SUM(D2:D' . ($j - 1) . ')');
	$xls->getActiveSheet()->setCellValue('E' . $j, '=SUM(E2:E' . ($j - 1) . ')');
	$xls->getActiveSheet()->setCellValue('F' . $j, '=SUM(F2:F' . ($j - 1) . ')');
	$xls->getActiveSheet()->setCellValue('G' . $j, '=SUM(G2:G' . ($j - 1) . ')');
	$xls->getActiveSheet()->setCellValue('H' . $j, '=SUM(H2:H' . ($j - 1) . ')');
	$xls->getActiveSheet()->setCellValue('I' . $j, '=SUM(I2:I' . ($j - 1) . ')');

	// Format Cells
	$xls->getActiveSheet()->getStyle('C0:C' . $j)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$xls->getActiveSheet()->getStyle('C0:C' . $j)->getFill()->getStartColor()->setARGB('FFFFFF00');
	$xls->getActiveSheet()->getStyle('D0:D' . $j)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$xls->getActiveSheet()->getStyle('D0:D' . $j)->getFill()->getStartColor()->setARGB('FFFFA500');
	$xls->getActiveSheet()->getStyle('E0:E' . $j)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$xls->getActiveSheet()->getStyle('E0:E' . $j)->getFill()->getStartColor()->setARGB('FFFF0000');
	$xls->getActiveSheet()->getStyle('G0:G' . $j)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$xls->getActiveSheet()->getStyle('G0:G' . $j)->getFill()->getStartColor()->setARGB('FFD0F5A9');
	$xls->getActiveSheet()->getStyle('H0:H' . $j)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$xls->getActiveSheet()->getStyle('H0:H' . $j)->getFill()->getStartColor()->setARGB('FF81F781');
	$xls->getActiveSheet()->getStyle('I0:I' . $j)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$xls->getActiveSheet()->getStyle('I0:I' . $j)->getFill()->getStartColor()->setARGB('FF01DF01');

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
