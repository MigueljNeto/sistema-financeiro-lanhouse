<?php
session_start();
require_once "../includes/servidor.php";

if(isset($_POST['valor'], $_POST['observacao'])){

    date_default_timezone_set('America/Sao_Paulo');

    $valorBruto = $_POST['valor'];
    $observacao = $_POST['observacao'];
    $data = date('Y-m-d H:i:s');

    $valor = str_replace(',', '.', $valorBruto);
    $valor = floatval($valor);

    if($valor <= 0){
        header("location: inicio.php?erro=valorinvalido");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO deposito (data, valor, observacao) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $data, $valor, $observacao);
    
    if ($stmt->execute()) {
        header("location: inicio.php?status=sucesso");
        exit;
    } else {
        header("location: inicio.php?status=erro=sql;");
        exit;
    }
    } else {
        header("location: inicio.php?erro=acesso");
        exit;
    }

?>