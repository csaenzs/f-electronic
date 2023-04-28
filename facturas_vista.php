<?php
include('facturas.php'); // Incluir el archivo de facturas
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
include('headers/header.php');
?>

<?php
/*
// Ordenar facturas por fecha de emisión en orden descendente
usort($facturas, function($a, $b) {
    return strtotime($b['fecha_emision']) - strtotime($a['fecha_emision']);
});
*/

// Primero, obtén la conexión a la base de datos
require_once 'db.php';

// Obtén el id del usuario de la sesión
$user_id = $_SESSION['user_id'];

// Obtener fechas de inicio y fin para filtrar facturas
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$formato_get = isset($_GET['formato']) ? $_GET['formato'] : '';
$tipo_tabla = isset($_GET['tipo_tabla']) ? $_GET['tipo_tabla'] : '';

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



?>

<!-- boton para regresar al inicio  -->

<button id="subir"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-arrow-up-square" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M15 2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2zM0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm8.5 9.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V11.5z"/>
</svg></button>

<!-- boton para regresar al inicio  -->

<div class="container mt-3">
    <h2 class="text-center">Facturas</h2>
    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filtrar-modal">
            Filtrar
        </button>
    </div>    


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


<!-- Modal para filtrar facturas -->
<div class="modal fade" id="filtrar-modal" tabindex="-1" aria-labelledby="filtrar-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filtrar-modal-label">Filtrar facturas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" placeholder=" " value="<?php echo $fecha_inicio; ?>">
                            <label for="fecha_inicio">Fecha de inicio</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" placeholder=" " value="<?php echo $fecha_fin; ?>">
                            <label for="fecha_fin">Fecha de fin</label>
                        </div>
                    </div>
                    <?php
                            // Realiza la consulta SQL para obtener los campos seleccionados del usuario
                            $sql2 = "SELECT nombre_formato FROM formatos WHERE id_usuario = :user_id";
                            $stmt2 = $pdo->prepare($sql2);
                            $stmt2->execute(['user_id' => $user_id]);
                            $resultado2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

                    ?>

                    <div class="col-12">

                    <div class="form-floating mb-3">
                        <select class="form-select" id="formato" name="formato" required>
                            <option value="">Selecciona un formato</option>
                            <?php foreach ($resultado2 as $f) { ?>
                                <option value="<?php echo $f['nombre_formato']; ?>"><?php echo $f['nombre_formato']; ?></option>
                            <?php } ?>
                        </select>
                        <label for="formato">Formato</label>
                    </div>

                    <div class="form-floating mb-3">
                        <select class="form-select" id="formato" name="tipo_tabla" required>
                            <option value="">Selecciona el tipo de tabla</option>
                            <option value="general">General</option>
                            <option value="detalle">Detallada</option>
                        </select>
                        <label for="formato">Tipo de tabla</label>
                    </div>


                </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<br><br><br><br><br>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css"/>



  <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>


    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.0/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.5/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.66/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.66/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.print.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.colVis.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.8/FileSaver.min.js"></script>

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
        ],
        "dom": 'Bfrtip',
        "buttons": [
          {
            extend: 'csv',
            text: 'Exportar a CSV',
            charset: 'utf-8',
            bom: true,
            filename: 'facturas'
          },
          {
            extend: 'excel',
            text: 'Exportar a Excel',
            charset: 'utf-8',
            bom: true,
            filename: 'facturas'
          }
        ]
      });
  
      $("div.toolbar").html('<div class="btn-group"><h5>Exportar a:</h5>' + $('#facturas-table').DataTable().buttons().container().prependTo($('div.toolbar')) + '</div>');
    });


        // Abrir el modal de filtrado cuando se hace clic en el botón de filtrado
        $('#filtrar-btn').click(function() {
            $('#filtrar-modal').modal('show');
        });
 
</script>
<?php 
    include('headers/footer.php');
?> 