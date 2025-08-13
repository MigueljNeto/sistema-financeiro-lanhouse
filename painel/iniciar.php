<?php
    session_start();
    require_once "../includes/servidor.php";


    $id = intval($_POST['id_maquina'] ?? 0);
    if ($id <= 0) { echo json_encode(['status'=>'erro','msg'=>'id inválido']); exit; }

    $res = mysqli_query($conn, "SELECT status, inicio FROM maquinas WHERE id=$id");
    $maq = mysqli_fetch_assoc($res);

    if (!$maq) { echo json_encode(['status'=>'erro','msg'=>'máquina não encontrada']); exit; }

    if ($maq['status'] !== 'ocupada') {
      
        $inicio = date('Y-m-d H:i:s');
        mysqli_query($conn, "UPDATE maquinas SET status='ocupada', inicio='$inicio' WHERE id=$id");
        $total = 0;
    } else {
      
        $inicio = $maq['inicio'];
        $stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(tempo_segundos),0) FROM tempo_uso WHERE maquina_id=? AND inicio >= ?");
        mysqli_stmt_bind_param($stmt, "is", $id, $inicio);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $salvo);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        $total = (int)$salvo + (time() - strtotime($inicio));
    }

    echo json_encode(['status'=>'ok','total_segundos'=>$total]);
?>