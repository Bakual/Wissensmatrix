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
foreach ($this->items AS $item) :
	// Create and set an active sheet
	if ($i) :
		$xls->createSheet();
	endif;
	$xls->setActiveSheetIndex($i);

	// Sanitize and shorten $item->title so it can be used as sheet title (max 31 chars allowed)
	$search = array(':', '\\', '/', '?', '*', '[', ']');
	$title  = str_replace($search, '_', $item->title);
	if (strlen($title) > 31) :
		$title = utf8_substr($title, 0, 28) . '...';
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
	if ($item->refresh) :
		$xls->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
		$xls->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
	endif;

	// Adding Header
	$xls->getActiveSheet()->SetCellValue('A1', 'User-ID');
	$xls->getActiveSheet()->SetCellValue('B1', JText::_('COM_WISSENSMATRIX_NACHNAME'));
	$xls->getActiveSheet()->SetCellValue('C1', JText::_('COM_WISSENSMATRIX_VORNAME'));
	$xls->getActiveSheet()->SetCellValue('D1', JText::_('COM_WISSENSMATRIX_TEAM'));
	$xls->getActiveSheet()->SetCellValue('E1', JText::_('COM_WISSENSMATRIX_STATE'));
	$xls->getActiveSheet()->SetCellValue('F1', JText::_('JDATE'));
	if ($item->refresh) :
		$xls->getActiveSheet()->SetCellValue('G1', JText::_('COM_WISSENSMATRIX_REFRESH'));
		$xls->getActiveSheet()->SetCellValue('H1', JText::_('COM_WISSENSMATRIX_TARGET_DATE'));
		$xls->getActiveSheet()->SetCellValue('I1', JText::_('COM_WISSENSMATRIX_FIELD_BEMERKUNG_LABEL'));
	else :
		$xls->getActiveSheet()->SetCellValue('G1', JText::_('COM_WISSENSMATRIX_FIELD_BEMERKUNG_LABEL'));
	endif;


	// Get Worker per WBI
	$this->w_state->set('wbi.id', $item->id);
	$workers = $this->workermodel->getItems();

	// Adding Data
	$j = 1;
	foreach ($workers AS $worker) :
		$j++;
		$xls->getActiveSheet()->SetCellValue('A' . $j, $worker->uid);
		$xls->getActiveSheet()->SetCellValue('B' . $j, $worker->name);
		$xls->getActiveSheet()->SetCellValue('C' . $j, $worker->vorname);
		$xls->getActiveSheet()->SetCellValue('D' . $j, $worker->category_title);
		$xls->getActiveSheet()->SetCellValue('E' . $j, JText::_('COM_WISSENSMATRIX_ZWBI_STATE_' . $worker->zwbi_status_id));
		$xls->getActiveSheet()->SetCellValue('F' . $j, $worker->date);
		if ($item->refresh) :
			$xls->getActiveSheet()->SetCellValue('G' . $j, $item->refresh);
			$xls->getActiveSheet()->SetCellValue('H' . $j, $worker->zwbi_refresh);
			$xls->getActiveSheet()->SetCellValue('I' . $j, $worker->bemerkung);
		else:
			$xls->getActiveSheet()->SetCellValue('G' . $j, $worker->bemerkung);
		endif;
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
header('Content-Disposition: attachment;filename="' . JText::_('COM_WISSENSMATRIX_WBIG') . '.xlsx"');

$xlsWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
$xlsWriter->save('php://output');
