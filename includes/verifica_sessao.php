<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    echo "voce nao esta logado";
    header("Location: ../login/index.php");
    exit;
}
?>