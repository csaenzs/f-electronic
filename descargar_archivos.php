<?php 
// Incluir la librería PhpSpreadsheet
require 'vendor/autoload.php';

// Crear la instancia del objeto de la hoja de cálculo
$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

// Agregar una nueva hoja de cálculo
$worksheet = $spreadsheet->getActiveSheet();

// Agregar las cabeceras de la tabla a la hoja de cálculo
$worksheet->setCellValue('A1', 'Emisor');
$worksheet->setCellValue('B1', 'NIT');
$worksheet->setCellValue('C1', 'Fecha de emisión');
$worksheet->setCellValue('D1', 'Hora de emisión');
$worksheet->setCellValue('E1', 'Subtotal');
$worksheet->setCellValue('F1', 'IVA');
$worksheet->setCellValue('G1', 'Total');
$worksheet->setCellValue('H1', 'Descripción de los ítems');

// Agregar los datos de las facturas a la hoja de cálculo
$row = 2;
foreach ($facturas as $factura) {
    $worksheet->setCellValue('A'.$row, $factura['emisor']);
    $worksheet->setCellValue('B'.$row, $factura['nit']);
    $worksheet->setCellValue('C'.$row, $factura['f_emision']);
    $worksheet->setCellValue('D'.$row, $factura['h_emision']);
    $worksheet->setCellValue('E'.$row, $factura['subtotal']);
    $worksheet->setCellValue('F'.$row, $factura['iva']);
    $worksheet->setCellValue('G'.$row, $factura['total']);
    $worksheet->setCellValue('H'.$row, $factura['items']);
    $row++;
}

// Verificar si se ha hecho clic en uno de los botones
if (isset($_GET['descargar'])) {
    $tipo = $_GET['descargar'];

    // Descargar como archivo de Excel
    if ($tipo == 'excel') {
       // Definir el tipo de archivo y el nombre de archivo de descarga
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="facturas.xls"');

        // Crear el escritor de la hoja de cálculo y descargar el archivo
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        $writer->save('php://output');
        exit();
    }
    // Descargar como archivo CSV
    elseif ($tipo == 'csv') {
        // Definir el tipo de archivo y el nombre de archivo de descarga
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="facturas.csv"');

        // Crear el escritor de la hoja de cálculo y descargar el archivo
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
        $writer->setDelimiter(',');
        $writer->setEnclosure('"');
        $writer->setLineEnding("\r\n");
        $writer->setSheetIndex(0);
        $writer->save('php://output');
        exit();
    }
}

?>