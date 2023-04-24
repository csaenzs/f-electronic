<?php
include('facturas.php'); // Incluir el archivo de facturas
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
include('headers/header.php');
?>

<?php
require_once 'db.php';

// Ordenar facturas por fecha de emisión en orden descendente
usort($facturas, function($a, $b) {
    return strtotime($b['fecha_emision']) - strtotime($a['fecha_emision']);
});

// Primero, obtén la conexión a la base de datos
require_once 'db.php';

// Obtén el id del usuario de la sesión
$user_id = $_SESSION['user_id'];

// Obtener fechas de inicio y fin para filtrar facturas
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$formato_get = isset($_GET['formato']) ? $_GET['formato'] : '';

if (empty($formato_get)) {
    // Mostrar alerta de Bootstrap 5 indicando que debe filtrar el formato previamente guardado y el rango de fechas
    echo '
    <div class="alert alert-warning text-center" role="alert">
        Debe filtrar el formato previamente guardado y el rango de fechas.
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

<div class="container mt-3">
    <h2 class="text-center">Facturas</h2>
    <div class="d-flex justify-content-end mb-3">
        <form action="descargar_archivos.php" method="POST">
            <input type="hidden" name="campos" value="<?php echo htmlspecialchars(json_encode($campos_seleccionados)); ?>">
            <input type="hidden" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
            <input type="hidden" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>">  
            <input type="hidden" name="descargar" value="csv">
            <button type="submit" class="btn btn-primary me-3">Descargar CSV</button>
        </form>

        <form action="descargar_archivos.php" method="POST">
            <input type="hidden" name="campos" value="<?php echo htmlspecialchars(json_encode($campos_seleccionados)); ?>">
            <input type="hidden" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
            <input type="hidden" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>">  
            <input type="hidden" name="descargar" value="excel">
            <button type="submit" class="btn btn-primary me-3">Descargar Excel</button>
        </form>

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
    <div class="table-responsive">
        <table id="facturas-table" class="table table-striped">
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
                            <td><?php echo empty($factura[$campo]) ? $campo : $factura[$campo]; ?></td>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>



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
                        <select class="form-select" id="formato" name="formato">
                            <option value="">Selecciona un formato</option>
                            <?php foreach ($resultado2 as $f) { ?>
                                <option value="<?php echo $f['nombre_formato']; ?>"><?php echo $f['nombre_formato']; ?></option>
                            <?php } ?>
                        </select>
                        <label for="formato">Formato</label>
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
<script>
    $(document).ready(function() {
        $('#facturas-table').DataTable();

        // Abrir el modal de filtrado cuando se hace clic en el botón de filtrado
        $('#filtrar-btn').click(function() {
            $('#filtrar-modal').modal('show');
        });
    });
</script>
<?php 
    include('headers/footer.php');
?> 