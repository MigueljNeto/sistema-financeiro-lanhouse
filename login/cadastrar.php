<?php
session_start();
require_once "../includes/servidor.php";

$email = trim($_POST['email']);
$senha = trim($_POST['senha']);
$senhaadm = trim($_POST['senhaadm']);

$senha_admim_correta = "admin123";

if ($senhaadm !== $senha_admim_correta) {
    echo "Senha do administrador incorreta,"; 
    exit;
}

$senha_criptografada = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO usuarios (email, senha) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $senha_criptografada);

if ($stmt->execute()) {
    echo "Usuario cadastrado com sucesso";
}else{
    echo "Erro ao cadastrar";
}
?>