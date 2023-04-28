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


        
            //ACTUALIZAR FOTO DE PERFIL
            
            // Verificar si se ha enviado una imagen
            if(isset($_FILES['profile_image'])) {
                
                // Verificar que la imagen sea válida
                if($_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                    
                    // Verificar que la imagen no exceda el tamaño máximo permitido (por ejemplo, 1MB)
                    if($_FILES['profile_image']['size'] <= 1000000) {
                        
                        // Guardar la imagen en una carpeta en el servidor
                        $image_name = uniqid() . '_' . $_FILES['profile_image']['name'];
                        move_uploaded_file($_FILES['profile_image']['tmp_name'], 'uploads/' . $image_name);
                        
                        // Obtener el ID del usuario actual
                        $user_id = $_SESSION['user_id'];
                        
                        // Guardar la URL de la imagen en la base de datos
                        $stmt = $pdo->prepare('UPDATE usuarios SET image = ? WHERE id = ?');
                        $stmt->execute(['uploads/' . $image_name, $user_id]);
                        
                        header('Location: perfil.php?estado=1');
                        
                    } else {
                        echo 'La imagen es demasiado grande.';
                    }
                    
                } else {
                    echo 'Se ha producido un error al subir la imagen.';
                }
                
            }


            if (isset($_GET['estado']) && $_GET['estado'] == 1) {
              echo '<div id="quitar-div-aler" class="alert alert-success d-flex justify-content-center align-items-center" role="alert">
              <span>Bien, Imagen de perfil modificada con éxito</span>
            </div>';
            }
            
?>

<!-- boton para regresar al inicio  -->

<button id="subir"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-arrow-up-square" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M15 2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2zM0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm8.5 9.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V11.5z"/>
</svg></button>

<!-- boton para regresar al inicio  -->

<section style="background-color: #eee;">
  <div class="container py-5">

    <div class="row">
      <div class="col-lg-4">
        <div class="card mb-4">
          <div class="card-body text-center">
            <img src="<?php echo $datos['image'] ?>" alt="avatar"
              class="rounded-circle img-fluid" style="width: 150px; height: 150px;">
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
                <p class="text-muted mb-0"><?php echo $datos['tel'] ?></p>
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

          <div class="col-md-12">
            <h2 class="text-center my-3">Cambiar Imagen de Perfil</h2>
            <form  method="post" enctype="multipart/form-data">
              <div class="mb-3">
                <input type="file" class="form-control" id="image" name="profile_image" accept="image/*" required>
              </div>
              <div class="text-center">
                <button type="submit" class="btn btn-primary">Cargar Imagen</button>
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>
  </div>
  <br><br><br><br><br>
</section>



<script>
        // Eliminar div despues de creado
        var div = document.getElementById('quitar-div-aler');
      setTimeout(function(){
          div.remove();
      }, 2000); // eliminar el div después de 5 segundos (5000 milisegundos)



</script>

<?php include('headers/footer.php'); ?>