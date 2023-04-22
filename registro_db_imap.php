<?php
session_start();
include 'db.php';

// Obtener los datos del formulario
$user_id = $_SESSION['user_id'];
$correo = filter_var($_POST["correo"], FILTER_SANITIZE_EMAIL);
$servidor = htmlspecialchars($_POST["servidor"]);
$puerto = htmlspecialchars($_POST["puerto"]);
$usuario = htmlspecialchars($_POST["usuario"]);
$password_imap = htmlspecialchars($_POST["password"]);

// Comprobar si el usuario ya existe
$sql_select = "SELECT * FROM usuarios WHERE id = :id";
$stmt_select = $pdo->prepare($sql_select);
$stmt_select->execute(array(':id' => $user_id));
$row = $stmt_select->fetch(PDO::FETCH_ASSOC);


if ($row) {
    // Actualizar los datos en la tabla
    $sql_update = "UPDATE usuarios SET host = :servidor, puerto = :puerto, user_imap = :usuario, password_imap = :password_imap WHERE correo_electronico_imap = :correo AND id = :id";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute(array(':servidor' => $servidor, ':puerto' => $puerto, ':usuario' => $usuario, ':password_imap' => $password_imap, ':correo' => $correo, ':id' => $user_id));

    header('Location: dashboard.php?imap=ok');
    
} else {
    echo "El usuario no existe en la base de datos";
}

$pdo = null;
?>
