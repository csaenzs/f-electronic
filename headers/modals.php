<?php 
 require_once 'db.php';

    // Preparamos la consulta SQL
    $sql = "SELECT * FROM usuarios WHERE id = :id";

    // Preparamos el statement
    $stmt = $pdo->prepare($sql);

    // Asignamos los valores a los parámetros de la consulta
    $stmt->bindParam(':id', $_SESSION["user_id"]);

    // Ejecutamos la consulta
    $stmt->execute();

    // Obtenemos los resultados en un arreglo asociativo
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!-- Modal registro de Correo Electronico -->
<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Formulario de configuración de servidor IMAP</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <form action="registro_db_imap.php" method="post">
        <div class="mb-3">
          <label for="correo" class="form-label">Correo electrónico:</label>
          <input type="text" name="correo" id="correo" class="form-control" value="<?php echo $datos['correo_electronico_imap'] ?>" required>
        </div>

        <div class="mb-3">
          <label for="servidor" class="form-label">Servidor IMAP:</label>
          <input type="text" name="servidor" id="servidor" class="form-control" value="<?php echo $datos['host'] ?>" required>
        </div>

        <div class="mb-3">
          <label for="puerto" class="form-label">Puerto:</label>
          <input type="number" name="puerto" id="puerto" class="form-control" value="<?php echo $datos['puerto'] ?>" required>
        </div>

        <div class="mb-3">
          <label for="usuario" class="form-label">Usuario IMAP:</label>
          <input type="text" name="usuario" id="usuario" class="form-control" value="<?php echo $datos['user_imap'] ?>" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Contraseña IMAP:</label>
          <input type="password" name="password" id="password" class="form-control" value="<?php echo $datos['password_imap'] ?>" required>
        </div>
      
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" value="Guardar" class="btn btn-primary">Guardar cambios</button>

      </form>
      </div>
    </div>
  </div>
</div>