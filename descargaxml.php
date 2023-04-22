<?php include('headers/header.php'); ?>

<center>
        <div id="loading" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5);">
    <p style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">Descargando Facturas... no recargues la página ni la cierres hasta que termine el proceso</p>
    <div class="text-center" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <img src="https://codigofuente.io/wp-content/uploads/2018/09/progress.gif" alt="Loading..." class="img-fluid" style="margin-top: -10px; width:15%;">
    </div>
</div>
      </center>
    

<?php


$facturas = array(); // Definir un array para guardar la información de las facturas

// Obtener las fechas de inicio y fin del formulario
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

if (!empty($fecha_inicio) && !empty($fecha_fin)) {

    include('conexion_correo.php');

    // Convertir las fechas al formato necesario para la búsqueda
    $fecha_inicio = date("d-M-Y", strtotime($fecha_inicio));
    $fecha_fin = date("d-M-Y", strtotime($fecha_fin));
    // Realizar la búsqueda de correos en el rango de fechas deseado
    $emails = imap_search($mailbox, 'SINCE "'.$fecha_inicio.'" BEFORE "'.$fecha_fin.'"');

    // Iterar sobre los correos encontrados y procesarlos como se desee
    if ($emails) {
        
        $facturas = array(); // Definir un array para guardar la información de las facturas
            $user_id = $_SESSION['user_id'];

            // Validar si la carpeta ya existe
            if (!is_dir('archivosXML/'.$user_id)) {
                // Crear la carpeta con el ID de usuario si no existe
                mkdir('archivosXML/'.$user_id, 0777, true);
            }

            foreach ($emails as $email_number) {
                $header = imap_headerinfo($mailbox, $email_number);
                $fecha = date("d-M-Y", strtotime($header->date));
                $structure = imap_fetchstructure($mailbox, $email_number);

                $attachments = array();
                if (isset($structure->parts) && count($structure->parts)) {
                    for ($j = 0; $j < count($structure->parts); $j++) {
                        $part = $structure->parts[$j];

                        if (isset($part->disposition) && $part->disposition == 'attachment' && strtolower(substr($part->dparameters[0]->value, -3)) === 'zip') {
                            $zip_data = base64_decode(imap_fetchbody($mailbox, $email_number, $j+1));
                            file_put_contents('temp.zip', $zip_data);
                            $zip = new ZipArchive;

                            if ($zip->open('temp.zip') === TRUE) {
                                for ($k = 0; $k < $zip->numFiles; $k++) {
                                    $filename = $zip->getNameIndex($k);

                                    if (strtolower(substr($filename, -3)) === 'xml') {
                                        $xmlfile = $zip->getFromIndex($k);

                                        $file_path = 'archivosXML/' . $user_id . '/' . $filename;
                                        file_put_contents($file_path, $xmlfile);

                                        // Agregar información de la factura al array de facturas
                                        $facturas[] = array(
                                            'fecha' => $fecha,
                                            'archivo' => $filename
                                        );
                                    }
                                }
                                $zip->close();
                            }
                        }
                    }
                }
            }

    }
} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Si no se ingresaron fechas, mostrar el formulario para que el usuario ingrese las fechas deseadas
    echo '<div class="d-flex justify-content-center">
            <form method="get" class="p-4 form-inline">
            <div class="row mb-6">
                <div class="col-md-4">
                <label for="fecha_inicio" class="form-label">Fecha de inicio:</label>
                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                </div>
                <div class="col-md-4">
                <label for="fecha_fin" class="form-label">Fecha de fin:</label>
                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                </div>
                <div style="margin-top:30px;" class="col-md-2 d-flex align-items-center">
                <input type="submit" class="btn btn-primary" id="descargar-facturas" value="Descargar Facturas">
                </div>    

            </div>
            </form>
        </div>';
} else {
    // Si se hizo una solicitud de método diferente a GET, mostrar un mensaje de error
    echo '<div class="alert alert-danger" role="alert">Método no permitido.</div>';
}

?>
<div class="container mt-4">
    <h1>Facturas procesadas</h1>
    <?php if ($facturas): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Archivo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($facturas as $factura): ?>
                    <tr>
                        <td><?= $factura['fecha'] ?></td>
                        <td><?= $factura['archivo'] ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">Total de archivos procesados: <?= count($facturas) ?></td>
                </tr>
            </tfoot>
        </table>
         <?php else: ?>
        <div class="alert alert-info" role="alert">
            No se encontraron facturas en el rango de fechas especificado.
        </div>
    <?php endif ?>
</div>

<!-- JavaScript -->
<script>
$(function() {
    $('#descargar-facturas').on('click', function() {
        // Mostrar la animación
        $('#loading').show();

        // Realizar la búsqueda de correos en el rango de fechas deseado
        $.get('buscar_facturas.php', { fecha_inicio: $('#fecha_inicio').val(), fecha_fin: $('#fecha_fin').val() }, function(data) {
            // Ocultar la animación una vez que se hayan cargado los datos de la tabla
            $('#loading').hide();
            $('#tabla-facturas').html(data);
        });
    });
});
</script>


<?php include('headers/footer.php'); ?>

