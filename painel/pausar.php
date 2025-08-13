<?php
    session_start();
    require_once "../includes/servidor.php";

    $id = intval($_POST['id_maquina'] ?? 0);
    $totalAtual = intval($_POST['total_segundos'] ?? 0);
    if ($id <= 0) { echo json_encode(['status'=>'erro']); exit; }


    $res = mysqli_query($conn, "SELECT inicio FROM maquinas WHERE id=$id AND status='ocupada'");
    $maq = mysqli_fetch_assoc($res);
    if (!$maq || !$maq['inicio']) { echo json_encode(['status'=>'erro','msg'=>'sem sessão ativa']); exit; }

    $inicioSessao = $maq['inicio'];


    $stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(tempo_segundos),0) FROM tempo_uso WHERE maquina_id=? AND inicio >= ?");
    mysqli_stmt_bind_param($stmt, "is", $id, $inicioSessao);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $jaSalvo);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    $delta = max(0, $totalAtual - (int)$jaSalvo);

    if ($delta > 0) {
        $fim = date('Y-m-d H:i:s');
        $inicioSegmento = date('Y-m-d H:i:s', time() - $delta);

        $stmt2 = mysqli_prepare($conn, "INSERT INTO tempo_uso (maquina_id, inicio, fim, tempo_segundos) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt2, "issi", $id, $inicioSegmento, $fim, $delta);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
    }

    echo json_encode(['status'=>'ok','delta_salvo'=>$delta]);
?>