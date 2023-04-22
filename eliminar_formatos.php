<?php
require_once('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_formato = $_POST['id_formato'] ?? '';

    // Eliminar el formato de la base de datos
    $stmt = $pdo->prepare("DELETE FROM formatos WHERE id = ?");
    $stmt->execute([$id_formato]);


}
?>


