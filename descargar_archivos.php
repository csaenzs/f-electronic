<?php 
require_once 'facturas.php';
// Incluir la librería PhpSpreadsheet..
require 'vendor/autoload.php';


// Obtener los datos del formulario
$campos = json_decode($_POST['campos'], true);
$fecha_inicio = $_POST['fecha_inicio'];
$fecha_fin = $_POST['fecha_fin'];

    // Filtrar facturas por rango de fechas
    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $facturas = array_filter($facturas, function($factura) use ($fecha_inicio, $fecha_fin) {
            return strtotime($factura['fecha_emision']) >= strtotime($fecha_inicio) && 
                   strtotime($factura['fecha_emision']) <= strtotime($fecha_fin);
        });
    }

// Crear la instancia del objeto de la hoja de cálculo
$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

// Agregar una nueva hoja de cálculo
$worksheet = $spreadsheet->getActiveSheet();

// Definir las cabeceras dinámicamente según los campos seleccionados
$headers = array();
foreach ($campos as $campo) {
    $headers[] = ucwords(str_replace(',', ' ', $campo));
}
$worksheet->fromArray($headers, NULL, 'A1');


// Agregar los datos de las facturas a la hoja de cálculo
$data = array();
foreach ($facturas as $factura) {
    $row = array();
    foreach ($campos as $campo) {
        $row[] = empty($factura[$campo]) ? '' : $factura[$campo];
    }
    $data[] = $row;
}
$worksheet->fromArray($data, NULL, 'A2');


// Verificar si se ha hecho clic en uno de los botones
if (isset($_POST['descargar'])) {
    $tipo = $_POST['descargar'];

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

            // Agregar la siguiente línea para incluir el BOM
            $writer->setUseBOM(true);

            ob_start(); // Capturar la salida en un buffer de salida
            $writer->save('php://output');
            $content = ob_get_clean(); // Obtener el contenido del buffer de salida
            echo $content; // Imprimir el contenido del archivo
            exit();
        }

}

?>
