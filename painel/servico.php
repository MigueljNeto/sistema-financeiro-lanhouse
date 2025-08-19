<?php
session_start();
require_once "../includes/servidor.php";

    date_default_timezone_set('America/Sao_Paulo');

    $usuario = $_POST['usuarios']?? '';
    $valor = $_POST['valor']?? '';
    $tipo_pagamento = $_POST['formas']?? '';
    $observacao = $_POST['observacao']?? '';
    $data = date("Y-m-d H:i:s");

    $valor = str_replace(',', '.', $valor);
    $valor = floatval($valor);

    if(empty($usuario)||empty($tipo_pagamento)){
        die("ERRO: é Obrigatorio selecionar o funcionario ou o tipo de pagamento.");
    }

    $sql = "INSERT INTO servicos (usuario, data, valor, tipo_pagamento, observacao)
        VALUES ('$usuario','$data', '$valor', '$tipo_pagamento', '$observacao')";
        
    if($conn->query($sql) === TRUE){
        header("location: inicio.php?status=sucesso");
        exit;
    }else{
        header("location: inicio.php?status=erro=sql;");
        exit;
    }

    $conn->close();
?>