<?php


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\NamedRange;

class Excel extends File
{
    static $sheets = array();

    function __construct()
    {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Hello World !');
        $inputFileName = './sampleData/example1.xls';

        /** Load $inputFileName to a Spreadsheet object **/
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);


    }


    function sheet()
    {

    }

    function save ( $name , $ext = 'xlsx' )
    {
        static $exts = array('xls','xml','xlsx','ods','slk','csv','gnumeric','html');
        if(! in_array($ext , $exts))
            throw new Exception('사용할 수 없는 확장자');
        $writer = new Xlsx($spreadsheet);
        $writer->save ( $name . '.' . $ext ) ;
    }

}


// Set up some basic data
$worksheet
    ->setCellValue('A1', 'Tax Rate:')
    ->setCellValue('B1', '=19%')
    ->setCellValue('A3', 'Net Price:')
    ->setCellValue('B3', 12.99)
    ->setCellValue('A4', 'Tax:')
    ->setCellValue('A5', 'Price including Tax:');

// Define named ranges
$spreadsheet->addNamedRange( new \PhpOffice\PhpSpreadsheet\NamedRange('TAX_RATE', $worksheet, '=$B$1') );
$spreadsheet->addNamedRange( new \PhpOffice\PhpSpreadsheet\NamedRange('PRICE', $worksheet, '=$B$3') );

// Reference that defined name in a formula
$worksheet
    ->setCellValue('B4', '=PRICE*TAX_RATE')
    ->setCellValue('B5', '=PRICE*(1+TAX_RATE)');



$worksheet
    ->setCellValue('A1', 'Tax Rate:')
    ->setCellValue('B1', '=TAX_RATE')
    ->setCellValue('A3', 'Net Price:')
    ->setCellValue('B3', 19.99)
    ->setCellValue('A4', 'Tax:')
    ->setCellValue('A5', 'Price including Tax:');

// Define a named range that we can use in our formulae
$spreadsheet->addNamedRange(new NamedRange('PRICE', $worksheet, '=$B$3'));

// Reference the defined formulae in worksheet formulae
$worksheet
    ->setCellValue('B4', '=TAX')
    ->setCellValue('B5', '=PRICE+TAX');








$worksheet = $spreadsheet->setActiveSheetIndex(0);
setYearlyData($worksheet,'2019', $data2019);
$worksheet = $spreadsheet->addSheet(new Worksheet($spreadsheet));
setYearlyData($worksheet,'2020', $data2020);
$worksheet = $spreadsheet->addSheet(new Worksheet($spreadsheet));
setYearlyData($worksheet,'2020', [], 'GROWTH');

function setYearlyData(Worksheet $worksheet, string $year, $yearlyData, ?string $title = null) {
    // Set up some basic data
    $worksheetTitle = $title ?: $year;
    $worksheet
        ->setTitle($worksheetTitle)
        ->setCellValue('A1', 'Month')
        ->setCellValue('B1', $worksheetTitle  === 'GROWTH' ? 'Growth' : 'Sales')
        ->setCellValue('C1', $worksheetTitle  === 'GROWTH' ? 'Profit Growth' : 'Margin')
        ->setCellValue('A2', Date::stringToExcel("{$year}-01-01"));
    for ($row = 3; $row <= 13; ++$row) {
        $worksheet->setCellValue("A{$row}", "=NEXT_MONTH");
    }

    if (!empty($yearlyData)) {
        $worksheet->fromArray($yearlyData, null, 'B2');
    } else {
        for ($row = 2; $row <= 13; ++$row) {
            $worksheet->setCellValue("B{$row}", "=GROWTH");
            $worksheet->setCellValue("C{$row}", "=PROFIT_GROWTH");
        }
    }

    $worksheet->getStyle('A1:C1')
        ->getFont()->setBold(true);
    $worksheet->getStyle('A2:A13')
        ->getNumberFormat()
        ->setFormatCode('mmmm');
    $worksheet->getStyle('B2:C13')
        ->getNumberFormat()
        ->setFormatCode($worksheetTitle  === 'GROWTH' ? '0.00%' : '_-€* #,##0_-');
}

// Add some Named Formulae
// The first to store our tax rate
$spreadsheet->addNamedFormula(new NamedFormula('NEXT_MONTH', $worksheet, "=EDATE(OFFSET(\$A1,-1,0),1)"));
$spreadsheet->addNamedFormula(new NamedFormula('GROWTH', $worksheet, "=IF('2020'!\$B1=\"\",\"-\",(('2020'!\$B1/'2019'!\$B1)-1))"));
$spreadsheet->addNamedFormula(new NamedFormula('PROFIT_GROWTH', $worksheet, "=IF('2020'!\$C1=\"\",\"-\",(('2020'!\$C1/'2019'!\$C1)-1))"));

for ($row = 2; $row<=7; ++$row) {
    $month = $worksheet->getCell("A{$row}")->getFormattedValue();
    $growth = $worksheet->getCell("B{$row}")->getFormattedValue();
    $profitGrowth = $worksheet->getCell("C{$row}")->getFormattedValue();

    echo "Growth for {$month} is {$growth}, with a Profit Growth of {$profitGrowth}", PHP_EOL;
}