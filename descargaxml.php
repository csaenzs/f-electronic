<?php include('headers/header.php'); ?>

     <center>
        <div id="loading" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 9999;">
        <p style="margin-top:90px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10000;">Descargando Facturas... no recargues la página ni la cierres hasta que termine el proceso</p>
            <div class="text-center" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                <img src="https://geekytheory.com/content/images/2015/02/loading.gif" alt="Loading..." class="img-fluid" style="margin-top: -10px; width:30%;">
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
                <input type="submit" class="btn btn-primary" id="descargar-facturas" value="Descargar Facturas" disabled>
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

<?php 

    require_once('db.php'); // Importa la conexión PDO

    $facturas_procesadas = 0;
    $facturas_registradas = 0;
    $facturas_ya_registradas = 0;

    //Guardar los datos en la tabla facturas_descargadas
    if (!empty($facturas)) { // Verifica que el array tenga datos

        // Itera sobre cada factura en el array
        foreach ($facturas as $factura) {
    
            // Verificar si la factura ya está registrada en la base de datos
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM facturas_descargadas WHERE nombre = ?");
            $stmt->execute([$factura['archivo']]);
            $result = $stmt->fetchColumn();

            if ($result > 0) {
                // La factura ya está registrada en la base de datos
                $facturas_ya_registradas++;
                continue;
            }

            // Prepara la consulta INSERT con parámetros
            $stmt = $pdo->prepare("INSERT INTO facturas_descargadas (fecha_descarga, fecha_factura, nombre, id_usuario) VALUES (?, ?, ?, ?)");

            // Asigna los valores de los parámetros
            $fecha_db = date('Y-m-d', strtotime($factura['fecha']));
            $nombre_db = $factura['archivo'];
            $id_usuario = $_SESSION['user_id']; // Aquí deberías asignar el ID de usuario correspondiente
            $fecha_actual = date('Y-m-d');

            try {
                $stmt->execute([$fecha_actual, $fecha_db, $nombre_db, $id_usuario]);
                $facturas_registradas++;
            } catch (PDOException $e) {
                echo "Error al guardar factura en la base de datos: " . $e->getMessage();
            }

            $facturas_procesadas++;
            
        }

        // Mensaje de éxito
        echo "<center><div class='alert alert-success' role='alert'>{$facturas_registradas} facturas registradas correctamente en la base de datos, y {$facturas_ya_registradas} facturas ya estaban registradas. Total de facturas procesadas: {$facturas_procesadas}</div></center>";
        
    } 

?>



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

    const btnDescargarFacturas = document.getElementById('descargar-facturas');
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');

    fechaInicio.addEventListener('change', validarCampos);
    fechaFin.addEventListener('change', validarCampos);

    function validarCampos() {
    if (fechaInicio.value && fechaFin.value) {
        btnDescargarFacturas.disabled = false;
    } else {
        btnDescargarFacturas.disabled = true;
    }
    }

    btnDescargarFacturas.addEventListener('click', (event) => {
    if (!fechaInicio.value || !fechaFin.value) {
        event.preventDefault();
        alert('Los campos de fecha no pueden estar vacíos');
    }
    });


</script>


<?php include('headers/footer.php'); ?>

