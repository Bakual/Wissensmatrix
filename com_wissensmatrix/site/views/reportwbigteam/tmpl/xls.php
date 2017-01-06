<?php
defined('_JEXEC') or die;

JHTML::addIncludePath(JPATH_COMPONENT . '/helpers');

// loading PHPExcel (http://www.codeplex.com/PHPExcel)
require_once JPATH_COMPONENT . '/libraries/PHPExcel.php';

$xls = new PHPExcel();

// Set default font
$xls->getDefaultStyle()->getFont()->setName('Arial');
$xls->getDefaultStyle()->getFont()->setSize(10);
$overviewSheet = $xls->getSheet(0);

// Prepare Overview Sheet
// Sanitize and shorten $item->title so it can be used as sheet title (max 31 chars allowed)
$search = array(':', '\\', '/', '?', '*', '[', ']');
$title  = str_replace($search, '_', JText::_('COM_WISSENSMATRIX_SUMMARY') . ' - ' . $this->item->title);

if (strlen($title) > 31) :
	$title = utf8_substr($title, 0, 28) . '...';
endif;

$overviewSheet->setTitle($title);

// Adding Header
$overviewSheet->setCellValue('A1', JText::_('COM_WISSENSMATRIX_WORKER'));
$overviewSheet->getColumnDimension('A')->setAutoSize(true);

$char      = 'A';
$column    = ord('A');
$workers   = $this->workermodel->getItems();
$workerRow = array();

$i = 1;
foreach ($workers as $worker) :
	$i++;
	$overviewSheet->setCellValue('A' . $i, $worker->name . ' ' . $worker->vorname);
	$workerRow[$worker->id] = $i;
endforeach;

$i = '1';
foreach ($this->items AS $item) :
	// Create and set an active sheet
	if ($i) :
		$xls->createSheet();
	endif;
	$sheet = $xls->getSheet($i);

	// Sanitize and shorten $item->title so it can be used as sheet title (max 31 chars allowed)
	$search = array(':', '\\', '/', '?', '*', '[', ']');
	$title  = str_replace($search, '_', $item->title);
	if (strlen($title) > 31) :
		$title = utf8_substr($title, 0, 28) . '...';
	endif;
	$sheet->setTitle($title);

	// Format Cells
	$sheet->getStyle('A1:I1')->getFont()->setBold(true);
	$sheet->getColumnDimension('A')->setAutoSize(true);
	$sheet->getColumnDimension('B')->setAutoSize(true);
	$sheet->getColumnDimension('C')->setAutoSize(true);
	$sheet->getColumnDimension('D')->setAutoSize(true);
	$sheet->getColumnDimension('E')->setAutoSize(true);
	$sheet->getColumnDimension('F')->setAutoSize(true);
	$sheet->getColumnDimension('G')->setAutoSize(true);
	if ($item->refresh) :
		$sheet->getColumnDimension('H')->setAutoSize(true);
		$sheet->getColumnDimension('I')->setAutoSize(true);
		$sheet->setAutoFilter('B1:H1');
	else :
		$sheet->setAutoFilter('B1:F1');
	endif;

	// Adding Header
	$sheet->SetCellValue('A1', 'User-ID');
	$sheet->SetCellValue('B1', JText::_('COM_WISSENSMATRIX_NACHNAME'));
	$sheet->SetCellValue('C1', JText::_('COM_WISSENSMATRIX_VORNAME'));
	$sheet->SetCellValue('D1', JText::_('COM_WISSENSMATRIX_TEAM'));
	$sheet->SetCellValue('E1', JText::_('COM_WISSENSMATRIX_STATE'));
	$sheet->SetCellValue('F1', JText::_('JDATE'));
	if ($item->refresh) :
		$sheet->SetCellValue('G1', JText::_('COM_WISSENSMATRIX_REFRESH'));
		$sheet->SetCellValue('H1', JText::_('COM_WISSENSMATRIX_TARGET_DATE'));
		$sheet->SetCellValue('I1', JText::_('COM_WISSENSMATRIX_FIELD_BEMERKUNG_LABEL'));
	else :
		$sheet->SetCellValue('G1', JText::_('COM_WISSENSMATRIX_FIELD_BEMERKUNG_LABEL'));
	endif;

	// Preparing Overview Sheet
	$column++;
	$char = chr($column);
	$overviewSheet->setCellValue($char . '1', $item->title);
	$overviewSheet->getStyle($char . '1')->getAlignment()->setTextRotation(90);
	$overviewSheet->getColumnDimension($char)->setWidth(10);

	// Get Worker per WBI
	$this->w_state->set('wbi.id', $item->id);
	$workers = $this->workermodel->getItems();

	// Adding Data
	$j = 1;
	foreach ($workers AS $worker) :
		$j++;
		$sheet->SetCellValue('A' . $j, $worker->uid);
		$sheet->SetCellValue('B' . $j, $worker->name);
		$sheet->SetCellValue('C' . $j, $worker->vorname);
		$sheet->SetCellValue('D' . $j, $worker->category_title);
		$sheet->SetCellValue('E' . $j, JText::_('COM_WISSENSMATRIX_ZWBI_STATE_' . $worker->zwbi_status_id));
		$sheet->SetCellValue('F' . $j, $worker->date);
		$sheet->getStyle('F' . $j)->getNumberFormat()->setFormatCode('dd.mm.yyyy hh:mm');
		$sheet->setCellValue('F' . $j, PHPExcel_Shared_Date::PHPToExcel(strtotime(JHtml::date($worker->date, 'Y-m-d h:m:s'))));
		if ($item->refresh) :
			$sheet->SetCellValue('G' . $j, $item->refresh);
			$sheet->SetCellValue('H' . $j, $worker->zwbi_refresh);
			$sheet->SetCellValue('I' . $j, $worker->bemerkung);
		else:
			$sheet->SetCellValue('G' . $j, $worker->bemerkung);
		endif;

		// Adding Data to Overview Sheet
		if (!isset($workerRow[$worker->id])) :
			continue;
		endif;

		$cell = $char . $workerRow[$worker->id];
		$overviewSheet->getStyle($cell)->getNumberFormat()->setFormatCode('dd.mm.yyyy');
		$overviewSheet->setCellValue($cell, PHPExcel_Shared_Date::PHPToExcel(strtotime(JHtml::date($worker->date, 'Y-m-d h:m:s'))));
		$color = ($worker->zwbi_status_id == 1) ? 'FAAC58' : '58FA58';
		$overviewSheet->getStyle($cell)->applyFromArray(
			array(
				'fill' => array(
					'type'  => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('rgb' => $color),
				),
			)
		);
	endforeach;
	$i++;
endforeach;

// Set the headers bold for Overview Sheet
$overviewSheet->getStyle('A1:' . $char . '1')->getFont()->setBold(true);
$overviewSheet->getStyle('B1:' . $char . '1')->getAlignment()->setHorizontal('center');
$overviewSheet->setAutoFilter('B1:' . $char . '1');

// Create a legend for the color on the Overview Sheet
$legendRow = count($workerRow) + 3;
$overviewSheet->setCellValue('A' . $legendRow, JText::_('COM_WISSENSMATRIX_LEGEND'));
$overviewSheet->getStyle('A' . $legendRow)->getFont()->setBold(true);
$overviewSheet->setCellValue('A' . ($legendRow + 1), JText::_('COM_WISSENSMATRIX_ZWBI_STATE_1'));
$overviewSheet->getStyle('A' . ($legendRow + 1))->applyFromArray(
	array(
		'fill' => array(
			'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array('rgb' => 'FAAC58'),
		),
	)
);
$overviewSheet->setCellValue('A' . ($legendRow + 2), JText::_('COM_WISSENSMATRIX_ZWBI_STATE_2'));
$overviewSheet->getStyle('A' . ($legendRow + 2))->applyFromArray(
	array(
		'fill' => array(
			'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array('rgb' => '58FA58'),
		),
	)
);

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
