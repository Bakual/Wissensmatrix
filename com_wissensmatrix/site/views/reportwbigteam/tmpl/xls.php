<?php
defined('_JEXEC') or die;

JHTML::addIncludePath(JPATH_COMPONENT.'/helpers');

// loading PHPExcel (http://www.codeplex.com/PHPExcel)
require_once JPATH_COMPONENT.'/libraries/PHPExcel.php';

$xls = new PHPExcel();

// Set default font
$xls->getDefaultStyle()->getFont()->setName('Arial');
$xls->getDefaultStyle()->getFont()->setSize(10);

$i = '0';
foreach ($this->items AS $item) :
	// Create and set an active sheet
	if ($i) :
		$xls->createSheet();
	endif;
	$xls->setActiveSheetIndex($i);

	// Sanitize and shorten $item->title so it can be used as sheet title (max 31 chars allowed)
	$search	= array(':', '\\', '/', '?', '*', '[', ']');
	$title	= str_replace($search, '_', $item->title);
	if (strlen($title) > 31) :
		$title	= substr($title, 0, 28).'...';
	endif;
	$xls->getActiveSheet()->setTitle($title);

	// Format Cells
	$xls->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);
	$xls->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
	$xls->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	$xls->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
	$xls->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
	$xls->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
	$xls->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
	$xls->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
	$xls->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);

	// Adding Header
	$xls->getActiveSheet()->SetCellValue('A1', 'User-ID');
	$xls->getActiveSheet()->SetCellValue('B1', JText::_('COM_WISSENSMATRIX_NACHNAME'));
	$xls->getActiveSheet()->SetCellValue('C1', JText::_('COM_WISSENSMATRIX_VORNAME'));
	$xls->getActiveSheet()->SetCellValue('D1', JText::_('COM_WISSENSMATRIX_TEAM'));
	$xls->getActiveSheet()->SetCellValue('E1', JText::_('COM_WISSENSMATRIX_IST').' '.JText::_('COM_WISSENSMATRIX_VALUE'));
	$xls->getActiveSheet()->SetCellValue('F1', JText::_('COM_WISSENSMATRIX_IST'));
	$xls->getActiveSheet()->SetCellValue('G1', JText::_('COM_WISSENSMATRIX_SOLL').' '.JText::_('COM_WISSENSMATRIX_VALUE'));
	$xls->getActiveSheet()->SetCellValue('H1', JText::_('COM_WISSENSMATRIX_SOLL'));

	// Adding Data
	$j = 1;
	foreach ($this->workers AS $worker) :
		$istsoll = $this->model->getIstSoll($item->id, $worker->id);
		if (!$istsoll['ist'] and !$istsoll['soll']) :
			continue;
		endif;
		$j++;
		$xls->getActiveSheet()->SetCellValue('A'.$j, $worker->uid);
		$xls->getActiveSheet()->SetCellValue('B'.$j, $worker->name);
		$xls->getActiveSheet()->SetCellValue('C'.$j, $worker->vorname);
		$xls->getActiveSheet()->SetCellValue('D'.$j, $worker->category_title);
		$xls->getActiveSheet()->SetCellValue('E'.$j, $istsoll['ist']);
		$xls->getActiveSheet()->SetCellValue('F'.$j, $istsoll['ist_title']);
		$xls->getActiveSheet()->SetCellValue('G'.$j, $istsoll['soll']);
		$xls->getActiveSheet()->SetCellValue('H'.$j, $istsoll['soll_title']);
	endforeach;

	$i++;
endforeach;

$xls->setActiveSheetIndex(0);

// generate File
header("200 HTTP/1.0 OK");
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.JText::_('COM_WISSENSMATRIX_WBIG').'.xlsx"');

$xlsWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
$xlsWriter->save('php://output');
