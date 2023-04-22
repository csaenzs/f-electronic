<?php
session_start();

// Borra todas las variables de sesión
$_SESSION = array();

// Si se desea matar la sesión, también se destruye la cookie de sesión.
// Nota: Esto destruirá la sesión, y no sólo los datos de la sesión.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, se destruye la sesión.
session_destroy();

// Redireccionar a la página de inicio de sesión
header("Location: sign-in/index.php");
exit();
?>
