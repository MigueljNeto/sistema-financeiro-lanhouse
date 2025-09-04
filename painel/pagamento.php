<?php
    session_start();
    require_once "../includes/servidor.php";

    if(isset($_POST['valor'], $_POST['observacao'], $_POST['tipos'])) {

    date_default_timezone_set('America/Sao_Paulo');

    $valorBruto = $_POST['valor'];
    $observacao = $_POST['observacao'];
    $tipo = $_POST['tipos']; 
    $data = date('Y-m-d H:i:s');

    $valor = str_replace(',', '.', $valorBruto);
    $valor = floatval($valor);

    if($valor <= 0){
        header("location: inicio.php?erro=valorinvalido");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO pagamento (tipo_pagamento, valor, observacao, data_hora) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $tipo, $valor, $observacao, $data);

    if ($stmt->execute()) {
        header("location: inicio.php?status=sucesso");
        exit;
    } else {
        header("location: inicio.php?status=erro_sql");
        exit;
    }

} else {
    header("location: inicio.php?erro=acesso");
    exit;
}
?>