<?php
defined('_JEXEC') or die;

JHTML::addIncludePath(JPATH_COMPONENT . '/helpers');

// loading PHPExcel (http://www.codeplex.com/PHPExcel)
require_once JPATH_COMPONENT . '/libraries/PHPExcel.php';

$xls = new PHPExcel();

// Set default font
$xls->getDefaultStyle()->getFont()->setName('Arial');
$xls->getDefaultStyle()->getFont()->setSize(10);
$xls->setActiveSheetIndex(0);

$count = count($this->workers);

// Format Cells
$xls->getActiveSheet()->getStyle('A1:' . $this->num2alpha(count($this->workers) + 1) . '1')->getFont()->setBold(true);
$xls->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$xls->getActiveSheet()->getColumnDimension('B')->setWidth(50);

// Adding Header
$xls->getActiveSheet()->SetCellValue('A1', JText::_('COM_WISSENSMATRIX_FWIG'));
$xls->getActiveSheet()->SetCellValue('B1', JText::_('COM_WISSENSMATRIX_FWI'));

$j = 1;
foreach ($this->workers as $worker) :
	$j++;
	$xls->getActiveSheet()->SetCellValue($this->num2alpha($j) . '1', $worker->uid);
endforeach;

// Adding Rows
$i = 1;
foreach ($this->items AS $item) :
	$i++;
	$xls->getActiveSheet()->SetCellValue('A' . $i, $item->fwig_title);
	$xls->getActiveSheet()->SetCellValue('B' . $i, $item->title);

	$ist = $this->model->getIst($item->id);

	$j = 1;
	foreach($this->workers as $worker) :
		$j++;
		if (isset($ist[$worker->id])) :
			$xls->getActiveSheet()->SetCellValue($this->num2alpha($j) . $i, $ist[$worker->id]['value']);
		endif;
	endforeach;
endforeach;

// generate File
header("200 HTTP/1.0 OK");
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . JText::_('COM_WISSENSMATRIX_EXPORT') . '.xlsx"');

$xlsWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
$xlsWriter->save('php://output');
