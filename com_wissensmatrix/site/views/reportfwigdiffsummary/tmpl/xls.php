<?php
defined('_JEXEC') or die;

JHTML::addIncludePath(JPATH_COMPONENT . '/helpers');

// loading PHPExcel (http://www.codeplex.com/PHPExcel)
require_once JPATH_COMPONENT . '/libraries/PHPExcel.php';

$xls = new PHPExcel();

// Set default font
$xls->getDefaultStyle()->getFont()->setName('Arial');
$xls->getDefaultStyle()->getFont()->setSize(10);

// Sanitize and shorten $item->title so it can be used as sheet title (max 31 chars allowed)
$search = array(':', '\\', '/', '?', '*', '[', ']');
$title  = str_replace($search, '_', JText::_('COM_WISSENSMATRIX_DIFF'));
if (strlen($title) > 31) :
	$title = substr($title, 0, 28) . '...';
endif;
$xls->getActiveSheet()->setTitle($title);

// Format Cells
$xls->getActiveSheet()->getStyle('A1:F1')->getFont()->setBold(true);
$xls->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$xls->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$xls->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$xls->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$xls->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$xls->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);

// Adding Header
$xls->getActiveSheet()->SetCellValue('A1', JText::_('COM_WISSENSMATRIX_FWIG'));
$xls->getActiveSheet()->SetCellValue('B1', JText::_('COM_WISSENSMATRIX_MANKO'));
$xls->getActiveSheet()->SetCellValue('C1', '%');
$xls->getActiveSheet()->SetCellValue('D1', JText::_('COM_WISSENSMATRIX_POTENTIAL'));
$xls->getActiveSheet()->SetCellValue('E1', '%');
$xls->getActiveSheet()->SetCellValue('F1', JText::_('COM_WISSENSMATRIX_BASE'));

// Adding Data
$i = 1;
foreach ($this->items as $item) :
	$i++;
	$xls->getActiveSheet()->SetCellValue('A' . $i, $item->title);
	$xls->getActiveSheet()->SetCellValue('B' . $i, isset($this->manko[$item->id]) ? $this->manko[$item->id]->mit_count : 0);
	$xls->getActiveSheet()->SetCellValue('C' . $i, '=ROUND(B' . $i . '/F' . $i . ', 2)');
	$xls->getActiveSheet()->SetCellValue('D' . $i, isset($this->potential[$item->id]) ? $this->potential[$item->id]->mit_count : 0);
	$xls->getActiveSheet()->SetCellValue('E' . $i, '=ROUND(D' . $i . '/F' . $i . ', 2)');
	$xls->getActiveSheet()->SetCellValue('F' . $i, isset($this->workers[$item->id]) ? $this->workers[$item->id]->mit_count : 0);
endforeach;

// Total is calculated by Excel
$i++;
$xls->getActiveSheet()->SetCellValue('A' . $i, JText::_('COM_WISSENSMATRIX_TOTAL'));
$xls->getActiveSheet()->SetCellValue('B' . $i, '=SUM(B2:B' . ($i - 1) . ')');
$xls->getActiveSheet()->SetCellValue('C' . $i, '=ROUND(B' . $i . '/F' . $i . ', 2)');
$xls->getActiveSheet()->SetCellValue('D' . $i, '=SUM(D2:D' . ($i - 1) . ')');
$xls->getActiveSheet()->SetCellValue('E' . $i, '=ROUND(D' . $i . '/F' . $i . ', 2)');
$xls->getActiveSheet()->SetCellValue('F' . $i, '=SUM(F2:F' . ($i - 1) . ')');

// Set "total" row to italic and set percent cell format
$xls->getActiveSheet()->getStyle('A' . $i . ':F' . $i)->getFont()->setItalic(true);
$xls->getActiveSheet()->getStyle('C2:C' . $i)->getNumberFormat()->setFormatCode('0%');
$xls->getActiveSheet()->getStyle('E2:E' . $i)->getNumberFormat()->setFormatCode('0%');

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
