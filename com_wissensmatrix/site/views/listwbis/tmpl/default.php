<?php
defined('_JEXEC') or die;

JHTML::addIncludePath(JPATH_COMPONENT . '/helpers');

// loading PHPExcel (http://www.codeplex.com/PHPExcel)
require_once JPATH_COMPONENT . '/libraries/PHPExcel.php';

$xls = new PHPExcel();

// Set default font
$xls->getDefaultStyle()->getFont()->setName('Arial');
$xls->getDefaultStyle()->getFont()->setSize(10);

// Format Cells
$xls->getActiveSheet()->getStyle('A1:I1')->getFont()->setBold(true);
$xls->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$xls->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$xls->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);

// Adding Header
$xls->getActiveSheet()->SetCellValue('A1', JText::_('COM_WISSENSMATRIX_WBIG'));
$xls->getActiveSheet()->SetCellValue('B1', JText::_('COM_WISSENSMATRIX_WBI'));
$xls->getActiveSheet()->SetCellValue('C1', JText::_('COM_WISSENSMATRIX_REFRESH'));

$i = 2;
foreach ($this->items AS $item) :
	$xls->getActiveSheet()->SetCellValue('A' . $i, $item->wbig_title);
	$xls->getActiveSheet()->SetCellValue('B' . $i, $item->title);
	$xls->getActiveSheet()->SetCellValue('C' . $i, $item->refresh);

	$i++;
endforeach;

$xls->setActiveSheetIndex(0);

// generate File
header("200 HTTP/1.0 OK");
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . JText::_('COM_WISSENSMATRIX_WBIS') . '.xlsx"');

$xlsWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
$xlsWriter->save('php://output');
