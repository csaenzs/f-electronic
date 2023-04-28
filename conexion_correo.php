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

// Conexión al servidor de correo
$host = $datos['host'];
$puerto = $datos['puerto'];
$email = $datos['user_imap'];

if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $domain = explode("@", $email)[1];
    if ($domain == "hotmail.com") {
        $imap_server = '{'.$host.':'.$puerto.'/imap/ssl}INBOX';
    } else {
        $imap_server = '{'.$host.':'.$puerto.'/imap/ssl/novalidate-cert}INBOX';
    }
  }

//$imap_server = '{'.$host.':'.$puerto.'/imap/ssl/novalidate-cert}INBOX';
$username = $datos['user_imap'];
$password = $datos['password_imap'];

// intenta conectarse al servidor de correo
$mailbox = imap_open($imap_server, $username, $password);

// comprueba si la conexión se realizó con éxito
if (!$mailbox) {
    // si la conexión falla, muestra el error correspondiente
    $error = imap_last_error();
    if (strpos($error, 'AUTHENTICATIONFAILED') !== false) {
        die('<div class="alert alert-warning alert-dismissible fade show" role="alert"><strong>Advertencia:</strong> La autenticación falló. Comprueba tu usuario y contraseña.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
    } elseif (strpos($error, 'AUTHORIZATIONFAILED') !== false) {
        die('<div class="alert alert-warning alert-dismissible fade show" role="alert"><strong>Advertencia:</strong> La autorización falló. Comprueba tu usuario y contraseña.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
    } elseif (strpos($error, 'Unknown error') !== false && strpos($error, 'Can\'t connect to') !== false) {
        die('<div class="alert alert-warning alert-dismissible fade show" role="alert"><strong>Advertencia:</strong> No se pudo conectar al servidor de correo. Comprueba el puerto y la dirección del servidor.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
    } else {
        die('<div class="alert alert-warning alert-dismissible fade show" role="alert"><strong>Advertencia:</strong> No se pudo conectar al servidor de correo: ' . $error . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
    }
}



?>