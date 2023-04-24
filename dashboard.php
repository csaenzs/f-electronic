<?php include('headers/header.php'); ?>

<?php
if (isset($_GET['imap']) && $_GET['imap'] == 'ok') {
  echo '<div id="quitar-div-aler" class="alert alert-success d-flex justify-content-center align-items-center" role="alert">
  <span>Bien, el Correo ha sido modificado con éxito</span>
</div>';
}

?>

<script>
        // Eliminar div despues de creado
        var div = document.getElementById('quitar-div-aler');
      setTimeout(function(){
          div.remove();
      }, 1500); // eliminar el div después de 5 segundos (5000 milisegundos)



</script>
 
<?php include('headers/footer.php'); ?>