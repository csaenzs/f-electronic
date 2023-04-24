<?php
session_start();
include 'db.php';

// Obtener los datos del formulario
$user_id = $_SESSION['user_id'];
$servidor = $_POST["servidor"];
$puerto = $_POST["puerto"];
$usuario = $_POST["usuario"];
$password_imap = $_POST["password"];

// Comprobar si el usuario ya existe
$sql_select = "SELECT * FROM usuarios WHERE id = :id";
$stmt_select = $pdo->prepare($sql_select);
$stmt_select->bindParam(':id', $user_id);
$stmt_select->execute();
$row = $stmt_select->fetch(PDO::FETCH_ASSOC);

if ($row) {
    // Actualizar los datos en la tabla
    $sql_update = "UPDATE usuarios SET host = :servidor, puerto = :puerto, user_imap = :usuario, password_imap = :password_imap WHERE id = :id";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->bindParam(':servidor', $servidor);
    $stmt_update->bindParam(':puerto', $puerto);
    $stmt_update->bindParam(':usuario', $usuario);
    $stmt_update->bindParam(':password_imap', $password_imap);
    $stmt_update->bindParam(':id', $user_id);
    $stmt_update->execute();

    header('Location: dashboard.php?imap=ok');
} else {
    echo "El usuario no existe en la base de datos";
}

$pdo = null;


?>
