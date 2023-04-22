<?php
session_start();
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE usuario = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $user['password'] === md5($password)) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['rol'];
        header('Location: ../dashboard.php');
        exit;
    } else {
        if ($user !== NULL && isset($user['estado'])) {
            $estado = $user['estado'];
        } else {
            $estado = "";
        }
        header("Location: index.php?alert=0");
        exit;
    }
}

require 'index.php';
?>
