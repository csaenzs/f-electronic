<?php
include('facturas.php'); // Incluir el archivo de facturas
?>

<?php
require_once 'db.php';

// Obtén el id del usuario de la sesión
$user_id = $_SESSION['user_id'];

// Obtener fechas de inicio y fin para filtrar facturas
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$formato_get = isset($_GET['formato']) ? $_GET['formato'] : '';
$tipo_tabla = isset($_GET['tipo_tabla']) ? $_GET['tipo_tabla'] : '';

// Cambiar el separador decimal a coma
setlocale(LC_NUMERIC, 'en_US');

if (empty($formato_get)) {

} else {
    // Realiza la consulta SQL para obtener los campos seleccionados del usuario
    $sql = "SELECT campos, nombre_formato FROM formatos WHERE id_usuario = :user_id AND nombre_formato = :formato_get";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id, 'formato_get' => $formato_get]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    // Decodificar el campo "campos" JSON
    $campos_seleccionados = json_decode($resultado['campos']);

    $campos_seleccionados_des = implode(',', $campos_seleccionados);

    // Filtrar facturas por rango de fechas
    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $facturas = array_filter($facturas, function($factura) use ($fecha_inicio, $fecha_fin) {
            return strtotime($factura['fecha_emision']) >= strtotime($fecha_inicio) && 
                   strtotime($factura['fecha_emision']) <= strtotime($fecha_fin);
        });
    }
}

?>

<?php
    // Nombre del archivo CSV
    $filename = "$formato_get.csv";
    
    // Establecer las cabeceras para descargar el archivo
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
            // Archivo donde se escribirá el contenido
        $fp = fopen('php://output', 'w');

        // Encabezados de la tabla
        $headers = array();
        foreach ($campos_seleccionados as $campo) {
            $headers[] = $campo;
        }
        $headers[] = 'Producto';
        $headers[] = 'Cantidad';
        $headers[] = 'Referencia';
        $headers[] = 'Porcentaje IVA';
        $headers[] = 'Precio Unitario sin IVA';
        $headers[] = 'IVA';
        $headers[] = 'Precio Unitario Incluido IVA';
        $headers[] = 'Total sin IVA';
        $headers[] = 'Total Incluido IVA';

        // Escribir los encabezados en el archivo CSV
        fputcsv($fp, $headers);

        // Escribir los datos de la tabla en el archivo CSV
        foreach ($facturas as $factura) {
            foreach ($factura['items_prod'] as $index => $producto) {
                $row = array();
                foreach ($campos_seleccionados as $campo) {
                    $row[] = empty($factura[$campo]) ? '' : $factura[$campo];
                }
                $row[] = $producto;
                $row[] = number_format($factura['qty_items'][$index], 2, ',', '');
                $row[] = $factura['ref_items'][$index];
                $row[] = number_format($factura['porcIVA_items'][$index], 2, ',', '');
                $row[] = number_format($factura['precio_items'][$index], 2, ',', '');
                $row[] = number_format($factura['iva_items'][$index], 2, ',', '');
                $row[] = number_format($factura['precio_items_incl_IVA'][$index], 2, ',', '');
                $row[] = number_format($factura['subtotal_items'][$index], 2, ',', '');
                $row[] = number_format($factura['precio_total_incl_IVA'][$index], 2, ',', '');
                fputcsv($fp, $row);
            }
        }

    // Cerrar el archivo
    fclose($fp);
?>