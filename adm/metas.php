<?php
    session_start();
    require_once "../includes/servidor.php";

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $metaDiaServico = $_POST['meta_dia_servicos'];
        $metaDiaPagamento = $_POST['meta_dia_pagamento'];
        $metaDiaMaquina = $_POST['meta_dia_maquina'];
        $metaMes = $_POST['meta_mes'];

        $stmt = $conn->prepare("INSERT INTO metas (meta_dia_servicos, meta_dia_pagamento, meta_dia_maquina, meta_mes) VALUES (?, ?, ?, ?)");
        $stmt ->bind_param("dddd", $metaDiaServico, $metaDiaMaquina, $metaDiaPagamento, $metaMes);

          if ($stmt->execute()) {
            header("location: configuracao.php?status=sucesso");
            exit;
        } else {
            header("location: configuracao.php?status=erro=sql;");
            exit;
        }
    
    }else {
        header("location: configuracao.php?erro=acesso");
        exit;
    }
?> 