<?php
include('facturas.php'); // Incluir el archivo de facturas

// Definir cabeceras para archivo Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=documento_exportado_" . date('Y:m:d:m:s') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Agregar la siguiente línea para que Excel interprete el archivo correctamente
echo "\xEF\xBB\xBF";


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
    // Mostrar alerta de Bootstrap 5 indicando que debe filtrar el formato previamente guardado y el rango de fechas
    echo '
    <div class="alert alert-warning text-center" role="alert">
        Debe filtrar el formato previamente guardado, el rango de fechas y el tipo de tabla que deseamostar.
    </div>
';


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

// Convertir los valores decimales de coma a punto
array_walk_recursive($facturas, function (&$value, $key) {
    if (is_numeric($value) && strpos($value, ',') !== false) {
        $value = str_replace(',', '.', $value);
    }
});

?>

<div class="container mt-3">
<?php if (count($facturas) == 0) { ?>
    <div class="alert alert-danger" role="alert">
        No se encontraron facturas que coincidan con los criterios de búsqueda. Verifica que hayas ejecutado el comando de descargar facturas.
        <a href="descargaxml.php" class="alert-link">Ir a la página de descarga de facturas</a>
    </div>
    <?php } else { ?>
    <?php if ($tipo_tabla == 'general') { ?>
        <div class="table-responsive">
        <table id="facturas-table" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <?php foreach ($campos_seleccionados as $campo) { ?>
                        <th><?php echo $campo; ?></th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($facturas as $factura) { ?>
                    <tr>
                        <?php foreach ($campos_seleccionados as $campo) { ?>
                            <td><?php echo empty($factura[$campo]) ? '' : $factura[$campo]; ?></td>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        </div>
    <?php } else if ($tipo_tabla == 'detalle') { ?>
        <div class="table-responsive">
            <table id="facturas-table" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <?php foreach ($campos_seleccionados as $campo) { ?>
                            <th><?php echo $campo; ?></th>
                        <?php } ?>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Referencia</th>
                        <th>Porcentaje IVA</th>
                        <th>Precio Unitario sin IVA</th>
                        <th>IVA</th>
                        <th>Precio Unitario Incluido IVA</th>
                        <th>Total sin IVA</th>
                        <th>Total Incluido IVA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($facturas as $factura) { ?>
                        <?php foreach ($factura['items_prod'] as $index => $producto) : ?>
                            <tr>
                                <?php foreach ($campos_seleccionados as $campo) { ?>
                                    <td><?php echo empty($factura[$campo]) ? '' : $factura[$campo]; ?></td>
                                <?php } ?>
                                <td><?= $producto ?></td>
                                <td><?= $factura['qty_items'][$index] ?></td>
                                <td><?= $factura['ref_items'][$index] ?></td>
                                <td><?= $factura['porcIVA_items'][$index] ?></td>
                                <td><?= $factura['precio_items'][$index] ?></td>
                                <td><?= $factura['iva_items'][$index] ?></td>
                                <td><?= $factura['precio_items_incl_IVA'][$index] ?></td>
                                <td><?= $factura['subtotal_items'][$index] ?></td>
                                <td><?= $factura['precio_total_incl_IVA'][$index] ?></td>
                            </tr>
                        <?php endforeach ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
<?php }  ?>

    <?php } ?>
</div>


<script>

$(document).ready(function() {
  $('#facturas-table').DataTable({
    "language": {
      "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/Spanish.json"
    },
    "paging": true,
    "pageLength": 10,
    "order": [[ 0, "desc" ]],
    "scrollX": true,
    "autoWidth": false,
    "columnDefs": [
      { "width": "10%", "targets": 0 },
      { "width": "20%", "targets": 1 },
      { "width": "30%", "targets": 2 },
      { "width": "40%", "targets": 3 },
      { "width": "10%", "targets": 4 }
    ]
  });
});


</script>
