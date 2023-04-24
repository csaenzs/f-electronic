<?php include('headers/header.php'); ?>

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


            //ACTUALIZAR CONTRASEÑA DE USUARIO 

    // Validar que las variables enviadas por POST no estén vacías
        if (!empty($_POST['password'])) {
            // Obtener el ID del usuario y la nueva contraseña
            $user_id = $_SESSION['user_id'];
            $new_password = $_POST['password'];
        
            // Encriptar la nueva contraseña en MD5
            $hashed_password = md5($new_password);
        
            // Actualizar la contraseña en la base de datos
            $sql = "UPDATE usuarios SET password = :password WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':id', $user_id);
        
            if ($stmt->execute()) {
                echo '<div id="quitar-div-aler" class="alert alert-success d-flex justify-content-center align-items-center" role="alert">
                <span>Bien, Contraseña actualizada correctamente</span>
              </div>';
            } else {
            echo "Error al actualizar la contraseña";
            }
        }

?>

<section style="background-color: #eee;">
  <div class="container py-5">

    <div class="row">
      <div class="col-lg-4">
        <div class="card mb-4">
          <div class="card-body text-center">
            <img src="https://github.com/mdo.png" alt="avatar"
              class="rounded-circle img-fluid" style="width: 150px;">
            <h5 class="my-3"><?php echo $datos['nombres'] ?></h5>
            <p class="text-muted mb-1"><?php if($datos['estado']==1){ echo '<p class="btn btn-primary" href="sign-in">->   Activo    <- </p>';} ?></p>
          </div>
        </div>

      </div>
      <div class="col-lg-8">
        <div class="card mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Nombre</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0"><?php echo $datos['nombres'] ?></p>
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Email</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0"><?php echo $datos['usuario'] ?></p>
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Telefono</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0">(097) 234-5678</p>
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Fecha de Creación</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0"><?php echo $datos['fecha_creacion'] ?></p>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="card mb-4 mb-md-0">

            <div class="container mt-4">
            <div class="row">
                    <div class="col-md-6 offset-md-3">
                    <h2>Cambiar Contraseña</h2>
                    <form action="" method="POST">

                        <div class="mb-3">
                        <label for="password" class="form-label">Nueva contraseña:</label>
                        <input type="password" class="form-control" id="password" name="password" required />
                        </div>
                        <button type="submit" class="btn btn-primary">Actualizar contraseña</button>
                        <br><br><br>
                    </form>
                    </div>
                </div>
                </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>

<?php include('headers/footer.php'); ?>