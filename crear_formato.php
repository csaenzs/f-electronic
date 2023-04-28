<?php
include('headers/header.php');
require_once 'db.php';

$stmt = $pdo->query("SELECT nombre FROM campos");
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_formato = $_POST['nombre_formato'] ?? '';
    $campos_seleccionados = isset($_POST['campos']) ? explode(',', $_POST['campos']) : [];

    echo '<meta http-equiv="refresh" content="0; url=guardar_formatos.php?campos=' . urlencode(implode(',', $campos_seleccionados)) . '&nombre_formato=' . urlencode($nombre_formato) . '">';
    exit;
}
?>

<?php
if (isset($_GET['estado']) && $_GET['estado'] == 1) {
  echo '<div id="quitar-div-aler" class="alert alert-success d-flex justify-content-center align-items-center" role="alert">
  <span>Bien, el formato ha sido guardado con éxito</span>
</div>';
}

if (isset($_GET['estado']) && $_GET['estado'] == 2) {
  echo '<div id="quitar-div-aler" class="alert alert-danger d-flex justify-content-center align-items-center" role="alert">
  <span>El formato ha sido Eliminado con éxito</span>
</div>
';
}



?>

<!-- boton para regresar al inicio  -->

<button id="subir"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-arrow-up-square" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M15 2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2zM0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm8.5 9.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V11.5z"/>
</svg></button>

<!-- boton para regresar al inicio  -->

<div class="container my-5">
<h2 class="mb-4 text-center">Selecciona los campos que necesitas en tu tabla</h2></br>
  <div class="row align-items-start">
    <!-- Columna izquierda para el formulario -->
    <div class="col-md-6">
      <form method="post">
        <div class="mb-3 d-flex align-items-center">         
          <select class="form-select flex-grow-1" id="campos-select">
            <option value="">Seleccione un campo de la Factura Electronica</option>
            <?php foreach ($resultado as $campo): ?>
              <option value="<?= $campo['nombre'] ?>"><?= $campo['nombre'] ?></option>
            <?php endforeach; ?>
          </select>
          <button type="button" class="btn btn-dark ms-3" id="agregar-campo-btn">Agregar</button>
        </div>

        <div class="mb-3 d-flex align-items-center">
        <input type="text" class="form-control flex-grow-1" id="campo-personalizado" placeholder="Agregar Campo personalizado">
        <button type="button" class="btn btn-dark ms-3" id="agregar-campo-personalizado-btn">Agregar</button>
      </div>


        <div class="mb-3">
          <label for="nombre_formato" class="form-label">Nombre Formato:</label>
          <input type="text" class="form-control" id="nombre_formato" name="nombre_formato">
        </div>


        <input type="hidden" id="campos" name="campos" value="">

        <button type="submit" class="btn btn-dark" name="submit">Guardar Formato</button>
      </form>
    </div>

    <!-- Columna derecha para la lista de campos agregados -->
    <div class="col-md-4 text-start">
      <h4 class="mb-3">Aquí se veran los campos agregados:</h4>
      <ul id="campos-list" class="list-group mb-3 sortable"></ul>
    </div>

  </div>

</div>

<!-- Mostrar el contenido de la tabla formatos -->

    <?php

    $id_usuario =  $_SESSION['user_id'];

    // Realizar consulta para obtener los datos de la tabla 'formatos'
    $sql = "SELECT * FROM formatos WHERE id_usuario = $id_usuario ";
    $stmt = $pdo->query($sql);

    // Obtener los resultados de la consulta
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

      <!-- Agregar una tabla para mostrar los resultados -->
      <div class="container my-5">
        <h2 class="mb-4 text-center">Formatos guardados</h2>
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre del formato</th>
              <th>Campos</th>              
              <th>Fecha de creación</th>
              <th>Eliminar Formato</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($resultados as $resultado): ?>
              <tr>
                <td><?= $resultado['id'] ?></td>
                <td><?= $resultado['nombre_formato'] ?></td>
                <td>
                  <?php 
                    // Eliminar las comillas dobles de los valores de los campos
                    $campos = str_replace(['[', ']', '"'], '', $resultado['campos']);
                    // Convertir la cadena en un arreglo de campos
                    $campos_array = explode(',', $campos);
                    // Mostrar cada campo en un botón
                    foreach ($campos_array as $campo):
                  ?>
                  <button type="button" class="btn btn-sm btn-outline-primary"><?= $campo ?></button>
                  <?php endforeach; ?>
                </td>      
                <td><?= $resultado['fecha_creacion'] ?></td>
                <td>
                <form class="eliminar-form" data-id="<?= $resultado['id'] ?>">
                    <button type="button" class="btn btn-danger eliminar-formato-btn">Eliminar</button>
                </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <br><br><br><br><br>

  <style>
      .btn-smaller {
        font-size: smaller;
        padding: 0.2rem 0.5rem;
      }

      .fa-bars.cursor-grab {
          cursor: grab;
        }
        .fa-bars.cursor-grabbing {
          cursor: grabbing;
        }

  </style>


<script>
$(function() {
  var campos_agregados = []; // variable para almacenar los campos agregados

  // Agregar campo cuando se hace clic en el botón
  $('#agregar-campo-btn').click(function() {
    var campo = $('#campos-select').val();
    if (campo !== '') {
      $('#campos-list').append('<li class="list-group-item"><label class="form-check-label d-flex align-items-start text-center pl-3"><input class="form-check-input" type="hidden" name="campos[]" value="' + campo + '"> ' + campo + '<button type="button" class="btn btn-danger eliminar-campo-btn ms-2 btn-smaller" data-campo="' + campo + '">Eliminar</button><i class="fa fa-bars ms-auto fa-2x cursor-grab"></i></label></li>');
      campos_agregados.push(campo); // agregar campo a la lista de campos agregados
    }
  });

  // Agregar campo personalizado cuando se hace clic en el botón
    $('#agregar-campo-personalizado-btn').click(function() {
      var campo = $('#campo-personalizado').val();
      if (campo !== '') {
        $('#campos-list').append('<li class="list-group-item"><label class="form-check-label d-flex align-items-start text-center pl-3"><input class="form-check-input" type="hidden" name="campos[]" value="' + campo + '"> ' + campo + '<button type="button" class="btn btn-danger eliminar-campo-btn ms-2 btn-smaller" data-campo="' + campo + '">Eliminar</button><i class="fa fa-bars ms-auto fa-2x cursor-grab"></i></label></li>');
        campos_agregados.push(campo); // agregar campo a la lista de campos agregados
      }
      $('#campo-personalizado').val('');
    });

  
  // Eliminar campo cuando se hace clic en la "X"
    $('#campos-list').on('click', '.eliminar-campo-btn', function() {
      var campo = $(this).data('campo');
      $(this).closest('li').remove();
      campos_agregados = campos_agregados.filter(function(value) {
        return value !== campo; // eliminar campo de la lista de campos agregados
      });
    });

    // Ordenar campos cuando se suelta un campo arrastrado
    $('#campos-list').sortable({
      update: function(event, ui) {
        campos_agregados = $('#campos-list').sortable('toArray', {attribute: 'data-campo'});
        $('#campos').val(campos_agregados.join(','));
      }
    });

    // Actualizar campos despues de ordenar las filas
    $('#campos-list').sortable({
      update: function(event, ui) {
        campos_agregados = [];
        $('#campos-list li').each(function() {
          campos_agregados.push($(this).find('.form-check-input').val());
        });
        $('#campos').val(campos_agregados.join(','));
      }
    });



  // Procesar el formulario
  $('form').submit(function() {
    // Obtener los campos seleccionados
    var campos = [];
    $('#campos-list .form-check-input').each(function() {
      if (this.checked) {
        campos.push(this.value);
      }
    });

    // Agregar campos agregados a la lista de campos a enviar
    campos = campos.concat(campos_agregados);

    // Establecer el valor del campo oculto
    $('#campos').val(campos.join(','));

    // Asegurarse de que al menos un campo esté seleccionado
    if (campos.length === 0) {
      alert('Debes seleccionar al menos un campo');
      return false;
    }    

    return true;
  });
});


// Funcion para eliminar datos de la tabla donde se muestran los formatos

  $(function() {
        $('.eliminar-formato-btn').click(function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            var id_formato = form.data('id');
            if (confirm('¿Estás seguro de que deseas eliminar este formato?')) {
                $.ajax({
                    type: 'POST',
                    url: 'eliminar_formatos.php',
                    data: { id_formato: id_formato },
                    success: function(response) {
                        form.closest('tr').remove();  
                        window.location.href = 'crear_formato.php?estado=2'; 
                                            
                    },
                    error: function(xhr, status, error) {
                        alert('Ocurrió un error al eliminar el formato.');
                    }
                });
            }
        });
    });

    // Eliminar div despues de creado
      var div = document.getElementById('quitar-div-aler');
      setTimeout(function(){
          div.remove();
      }, 1500); // eliminar el div después de 5 segundos (5000 milisegundos)


</script>


<?php include('headers/footer.php'); ?>
