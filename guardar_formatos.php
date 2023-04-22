<?php
session_start();
require_once 'db.php';

// Obtener el nombre del formato desde la URL
$nombre_formato = isset($_GET['nombre_formato']) ? $_GET['nombre_formato'] : '';

// Obtenemos los campos seleccionados
$campos_seleccionados = isset($_GET['campos']) ? explode(',', $_GET['campos']) : array();

// Obtener el id del usuario desde la sesión
$id_usuario = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Insertar los datos en la tabla de formatos
$sql = "INSERT INTO formatos (nombre_formato, campos, id_usuario, fecha_creacion) VALUES (:nombre_formato, :campos, :id_usuario, NOW())";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':nombre_formato', $nombre_formato);
$stmt->bindParam(':campos', json_encode($campos_seleccionados));
$stmt->bindParam(':id_usuario', $id_usuario);
$stmt->execute();

header('Location: crear_formato.php?estado=1');
exit;

?>
