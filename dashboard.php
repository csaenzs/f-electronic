<?php include('headers/header.php'); ?>

<?php
if (isset($_GET['imap']) && $_GET['imap'] == 'ok') {
  echo '<div id="quitar-div-aler" class="alert alert-success d-flex justify-content-center align-items-center" role="alert">
  <span>Bien, el Correo ha sido modificado con éxito</span>
</div>';
}

if (isset($_GET['estado']) && $_GET['estado'] == 2) {
  echo '<div id="quitar-div-aler" class="alert alert-danger d-flex justify-content-center align-items-center" role="alert">
  <span>El formato ha sido Eliminado con éxito</span>
</div>
';
}

require_once 'db.php';
$user_id = $_SESSION['user_id'];
?>

<?php


// Consulta tabla formatos 
$sql = "SELECT * FROM formatos WHERE id_usuario = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

// Obtener los resultados como un arreglo asociativo
$results_formatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener el número total de resultados
$total_formatos = $stmt->rowCount();


//Consultar la tabla Facturas_descargadas

    // Consulta de facturas descargadas hoy
    $sql_today = "SELECT * FROM facturas_descargadas 
                  WHERE id_usuario = :user_id AND DATE(fecha_descarga) = CURDATE()";
    $stmt_today = $pdo->prepare($sql_today);
    $stmt_today->bindParam(':user_id', $user_id);
    $stmt_today->execute();
    $facturas_hoy = $stmt_today->fetchAll(PDO::FETCH_ASSOC);
    $total_hoy = count($facturas_hoy);

    // Consulta de facturas descargadas esta semana
    $sql_week = "SELECT * FROM facturas_descargadas 
                WHERE id_usuario = :user_id AND YEARWEEK(fecha_descarga) = YEARWEEK(CURDATE())";
    $stmt_week = $pdo->prepare($sql_week);
    $stmt_week->bindParam(':user_id', $user_id);
    $stmt_week->execute();
    $facturas_semana = $stmt_week->fetchAll(PDO::FETCH_ASSOC);
    $total_semana = count($facturas_semana);

    // Consulta de facturas descargadas este mes
    $sql_month = "SELECT * FROM facturas_descargadas 
                  WHERE id_usuario = :user_id AND YEAR(fecha_descarga) = YEAR(CURDATE()) 
                  AND MONTH(fecha_descarga) = MONTH(CURDATE())";
    $stmt_month = $pdo->prepare($sql_month);
    $stmt_month->bindParam(':user_id', $user_id);
    $stmt_month->execute();
    $facturas_mes = $stmt_month->fetchAll(PDO::FETCH_ASSOC);
    $total_mes = count($facturas_mes);


?>


<div class="container py-4">
  <div class="row">
          <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
              <div class="card-header p-3 pt-2">
                <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
                
                </div>
                <div class="text-end pt-1">
                  <p class="text-sm mb-0 text-capitalize">Facturas Descargasdas Hoy</p>
                  <h4 class="mb-0"><?php echo $total_hoy; ?></h4>
                </div>
              </div>
              <hr class="dark horizontal my-0">
              <div class="card-footer p-3">
                <p class="mb-0"><span class="text-success text-sm font-weight-bolder"><a href="dashboard.php?mas=hoy">Ver más</a></span></p>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
              <div class="card-header p-3 pt-2">
                <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                
                </div>
                <div class="text-end pt-1">
                  <p class="text-sm mb-0 text-capitalize">Facturas descargadas esta semana</p>
                  <h4 class="mb-0"><?php echo $total_semana; ?></h4>
                </div>
              </div>
              <hr class="dark horizontal my-0">
              <div class="card-footer p-3">
              <p class="mb-0"><span class="text-success text-sm font-weight-bolder"><a href="dashboard.php?mas=semana">Ver más</a></span></p>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
              <div class="card-header p-3 pt-2">
                <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                
                </div>
                <div class="text-end pt-1">
                  <p class="text-sm mb-0 text-capitalize">Facturas descargadas este mes</p>
                  <h4 class="mb-0"><?php echo $total_mes; ?></h4>
                </div>
              </div>
              <hr class="dark horizontal my-0">
              <div class="card-footer p-3">
              <p class="mb-0"><span class="text-success text-sm font-weight-bolder"><a href="dashboard.php?mas=mes">Ver más</a></span></p>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-sm-6">
            <div class="card">
              <div class="card-header p-3 pt-2">
                <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                
                </div>
                <div class="text-end pt-1">
                  <p class="text-sm mb-0 text-capitalize">Total Formatos creados</p>
                  <h4 class="mb-0"><?php echo $total_formatos; ?></h4>
                </div>
              </div>
              <hr class="dark horizontal my-0">
              <div class="card-footer p-3">
              <p class="mb-0"><span class="text-success text-sm font-weight-bolder"><a href="dashboard.php?mas=formatos">Ver más</a></span></p>
              </div>
            </div>
          </div>
        </div>

</div>

<?php if (isset($_GET['mas']) && $_GET['mas'] == 'formatos') { ?>
        <div class="container my-5">
            <table id="formatos-table" class="display nowrap" style="width:100%">
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
                  <?php foreach ($results_formatos as $formatos): ?>
                    <tr>
                      <td><?= $formatos['id'] ?></td>
                      <td><?= $formatos['nombre_formato'] ?></td>
                      <td>
                        <?php 
                          // Eliminar las comillas dobles de los valores de los campos
                          $campos = str_replace(['[', ']', '"'], '', $formatos['campos']);
                          // Convertir la cadena en un arreglo de campos
                          $campos_array = explode(',', $campos);
                          // Mostrar cada campo en un botón
                          foreach ($campos_array as $campo):
                        ?>
                        <button type="button" class="btn btn-sm btn-outline-primary"><?= $campo ?></button>
                        <?php endforeach; ?>
                      </td>      
                      <td><?= $formatos['fecha_creacion'] ?></td>
                      <td>
                      <form class="eliminar-form" data-id="<?= $formatos['id'] ?>">
                          <button type="button" class="btn btn-danger eliminar-formato-btn">Eliminar</button>
                      </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
            </table>
        </div>
<?php } ?>

<?php if (isset($_GET['mas']) && $_GET['mas'] == 'hoy') { ?>
        <div class="container my-5">
          <table id="formatos-table" class="display nowrap" style="width:100%">
                  <thead>
                      <tr>
                      <th>ID</th>
                      <th>Nombre de la factura</th>                            
                      <th>Fecha de la descarga</th>                    
                      </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($facturas_hoy as $factura): ?>
                      <tr>
                        <td><?php echo $factura['id'] ?></td>
                        <td><?php echo $factura['nombre'] ?></td>
                        <td><?php echo $factura['fecha_descarga'] ?></td>
                      </tr>

                    <?php endforeach; ?>
                  </tbody>
              </table>           
        </div>
<?php } ?>

<?php if (isset($_GET['mas']) && $_GET['mas'] == 'semana') { ?>
  <div class="container my-5">
          <table id="formatos-table" class="display nowrap" style="width:100%">
                  <thead>
                      <tr>
                      <th>ID</th>
                      <th>Nombre de la factura</th>                            
                      <th>Fecha de la descarga</th>                    
                      </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($facturas_semana as $factura): ?>
                      <tr>
                        <td><?php echo $factura['id'] ?></td>
                        <td><?php echo $factura['nombre'] ?></td>
                        <td><?php echo $factura['fecha_descarga'] ?></td>
                      </tr>

                    <?php endforeach; ?>
                  </tbody>
              </table>           
        </div>
<?php } ?>

<?php if (isset($_GET['mas']) && $_GET['mas'] == 'mes') { ?>
  <div class="container my-5">
          <table id="formatos-table" class="display nowrap" style="width:100%">
                  <thead>
                      <tr>
                      <th>ID</th>
                      <th>Nombre de la factura</th>                            
                      <th>Fecha de la descarga</th>                    
                      </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($facturas_mes as $factura): ?>
                      <tr>
                        <td><?php echo $factura['id'] ?></td>
                        <td><?php echo $factura['nombre'] ?></td>
                        <td><?php echo $factura['fecha_descarga'] ?></td>
                      </tr>

                    <?php endforeach; ?>
                  </tbody>
              </table>           
        </div>
<?php } ?>



<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>

<script>

  $(document).ready(function() {
    $('#formatos-table').DataTable({
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
                        window.location.href = 'dashboard.php?estado=2'; 
                                            
                    },
                    error: function(xhr, status, error) {
                        alert('Ocurrió un error al eliminar el formato.');
                    }
                });
            }
        });
    });



          // Abrir el modal de filtrado cuando se hace clic en el botón de filtrado
          $('#filtrar-btn').click(function() {
              $('#filtrar-modal').modal('show');
          });
  
</script>


<script>
        // Eliminar div despues de creado
        var div = document.getElementById('quitar-div-aler');
      setTimeout(function(){
          div.remove();
      }, 1500); // eliminar el div después de 5 segundos (5000 milisegundos)



</script>
 
<?php include('headers/footer.php'); ?>