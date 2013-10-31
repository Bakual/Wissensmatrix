<?php
defined('_JEXEC') or die;

JHTML::addIncludePath(JPATH_COMPONENT.'/helpers');

// loading PHPExcel (http://www.codeplex.com/PHPExcel)
require_once JPATH_COMPONENT.'/libraries/PHPExcel.php';

$xls = new PHPExcel();

// Set default font
$xls->getDefaultStyle()->getFont()->setName('Arial');
$xls->getDefaultStyle()->getFont()->setSize(10);

// Sanitize and shorten $item->title so it can be used as sheet title (max 31 chars allowed)
$search	= array(':', '\\', '/', '?', '*', '[', ']');
$title	= str_replace($search, '_', JText::_('COM_WISSENSMATRIX_LEVELS'));
if (strlen($title) > 31) :
	$title	= substr($title, 0, 28).'...';
endif;
$xls->getActiveSheet()->setTitle($title);

// Format Cells
$xls->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$column	= ord('A');
foreach ($this->levels as $level) :
	if (!$level->value) continue;
	$column++;
	$xls->getActiveSheet()->getColumnDimension(chr($column))->setAutoSize(true);
	$column++;
	$xls->getActiveSheet()->getColumnDimension(chr($column))->setAutoSize(true);
	$column++;
	$xls->getActiveSheet()->getColumnDimension(chr($column))->setAutoSize(true);
endforeach;
$xls->getActiveSheet()->getStyle('A1:'.chr($column).'1')->getFont()->setBold(true);

// Adding Header
$xls->getActiveSheet()->SetCellValue('A1', JText::_('COM_WISSENSMATRIX_FWIG'));
$column	= ord('A');
foreach ($this->levels as $level) :
	if (!$level->value) continue;
	$column++;
	$xls->getActiveSheet()->SetCellValue(chr($column).'1', $level->title.' '.JText::_('COM_WISSENSMATRIX_IST'));
	$column++;
	$xls->getActiveSheet()->SetCellValue(chr($column).'1', $level->title.' '.JText::_('COM_WISSENSMATRIX_SOLL'));
	$column++;
	$xls->getActiveSheet()->SetCellValue(chr($column).'1', $level->title.' %');
endforeach;

// Adding Data
$i = 1;
foreach ($this->items as $item) :
	$i++;
	$xls->getActiveSheet()->SetCellValue('A'.$i, $item->title);
	$column	= ord('A');
	$perc	= array();
	foreach ($this->levels as $key => $level) :
		if (!$level->value) continue;
		$ist	= (isset($this->ist[$key][$item->id])) ? $this->ist[$key][$item->id]->mit_count : 0;
		$soll	= (isset($this->soll[$key][$item->id])) ? $this->soll[$key][$item->id]->mit_count : 0;
		$column++;
		$xls->getActiveSheet()->SetCellValue(chr($column).$i, $ist);
		$column++;
		$xls->getActiveSheet()->SetCellValue(chr($column).$i, $soll);
		$column++;
		$xls->getActiveSheet()->SetCellValue(chr($column).$i, '=IF('.chr($column-1).$i.'=0,"n/a",ROUND(('.chr($column-2).$i.'/'.chr($column-1).$i.'), 2))');
	endforeach;
endforeach;

// Total is calculated by Excel
$i++;
$xls->getActiveSheet()->SetCellValue('A'.$i, JText::_('COM_WISSENSMATRIX_TOTAL'));
$column	= ord('A');
foreach ($this->levels as $key => $level) :
	if (!$level->value) continue;
	$column++;
	$c = chr($column);
	$xls->getActiveSheet()->SetCellValue($c.$i, '=SUM('.$c.'2:'.$c.($i-1).')');
	$column++;
	$c = chr($column);
	$xls->getActiveSheet()->SetCellValue($c.$i, '=SUM('.$c.'2:'.$c.($i-1).')');
	$column++;
	$c = chr($column);
	$xls->getActiveSheet()->SetCellValue($c.$i, '=IF('.chr($column-1).$i.'=0,"n/a",ROUND(('.chr($column-2).$i.'/'.chr($column-1).$i.'), 2))');
	$perc[]	= $column;
endforeach;

// Set "total" row to italic and set percent cell format
$xls->getActiveSheet()->getStyle('A'.$i.':'.chr($column).$i)->getFont()->setItalic(true);
foreach ($perc as $col)
{
	$xls->getActiveSheet()->getStyle(chr($col).'2:'.chr($col).$i)->getNumberFormat()->setFormatCode('0%');
}

$xls->setActiveSheetIndex(0);

// generate File
header("200 HTTP/1.0 OK");
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.JText::_('COM_WISSENSMATRIX_FWIG').'.xlsx"');

$xlsWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
$xlsWriter->save('php://output');
