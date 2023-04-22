<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.84.0">
    <title>Login</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/5.0/examples/sign-in/">

    <!-- Bootstrap core CSS -->
    <link href="../assets/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
      
    </style>

    <!-- Custom styles for this template -->
    <link href="signin.css" rel="stylesheet">
  </head>
  <body class="text-center">
    
    <main class="form-signin">
      <form method="post" action="login_controller.php">
      <?php 

        if(isset($_GET["alert"])) {

            echo '  <div class="alert alert-danger">
            <strong>Alerta!</strong> Usuario o contraseña Incorrecto</div>';
              
          }

        ?>
        <img class="mb-4" src="../assets/brand/logo.png" alt="" width="170" height="170">
        <h1 class="h3 mb-3 fw-normal">Por favor inicia Sesión</h1>

        <div class="form-floating">
          <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com" name="email">
          <label for="floatingInput">Correo</label>
        </div>
        <div class="form-floating">
          <input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password">
          <label for="floatingPassword">Password</label>
        </div>

        <div class="checkbox mb-3">
          <label>
            <input type="checkbox" value="remember-me"> Recodar mis datos
          </label>
        </div>
        <button class="w-100 btn btn-lg btn-dark" type="submit">Iniciar Sesión</button>
        <p class="mt-5 mb-3 text-muted">Todos los derechos reservados&copy; </br> 2023</p>
      </form>
    </main>

    <!-- Bootstrap core JS -->
    <script src="../assets/dist/js/bootstrap.bundle.min.js"></script>

  </body>
</html>
