<?php
session_start();
require_once "../includes/servidor.php";

$email = "";
$senha = "";

$email = trim($_POST['email']);
$senha = trim($_POST['senha']);

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 1){
    $usuario = $resultado->fetch_assoc();
    $senhaDoBanco = $usuario ['senha'];
}

if (password_verify($senha, $senhaDoBanco)){
    $_SESSION['usuario'] = $usuario['email'];
    header("location:../painel/inicio.php");
}else{
    echo "<script>alert('Senha incorreta ou E-mail invalido!'); window.history.back();</script>";
}
?>