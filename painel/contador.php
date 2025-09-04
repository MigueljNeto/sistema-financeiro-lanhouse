<?php
    session_start();
    require_once "../includes/servidor.php";

    $acao = $_POST['acao'] ?? '';
    $id   = intval($_POST['id_maquina'] ?? 0);
    $totalAtual = intval($_POST['total_segundos'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['status' => 'erro', 'msg' => 'ID inválido']);
        exit;
    }

switch ($acao) {

    // ─────────────── INICIAR ───────────────
    case 'iniciar':
        $res = mysqli_query($conn, "SELECT status, inicio FROM maquinas WHERE id=$id");
        $maq = mysqli_fetch_assoc($res);

        if (!$maq) {
            echo json_encode(['status'=>'erro','msg'=>'máquina não encontrada']);
            exit;
        }

        if ($maq['status'] !== 'ocupada') {
            $inicio = date('Y-m-d H:i:s');
            mysqli_query($conn, "UPDATE maquinas SET status='ocupada', inicio='$inicio' WHERE id=$id");
            $total = 0;
        } else {
            $inicio = $maq['inicio'];
            $stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(tempo_segundos),0) 
                                           FROM tempo_uso 
                                           WHERE maquina_id=? AND inicio >= ?");
            mysqli_stmt_bind_param($stmt, "is", $id, $inicio);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $salvo);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);
            $total = (int)$salvo + (time() - strtotime($inicio));
        }

        echo json_encode(['status'=>'ok','total_segundos'=>$total]);
        break;

    // ─────────────── PAUSAR ───────────────
    case 'pausar':
        $res = mysqli_query($conn, "SELECT inicio FROM maquinas WHERE id=$id AND status='ocupada'");
        $maq = mysqli_fetch_assoc($res);
        if (!$maq || !$maq['inicio']) {
            echo json_encode(['status'=>'erro','msg'=>'sem sessão ativa']);
            exit;
        }

        $inicioSessao = $maq['inicio'];

        $stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(tempo_segundos),0) 
                                       FROM tempo_uso 
                                       WHERE maquina_id=? AND inicio >= ?");
        mysqli_stmt_bind_param($stmt, "is", $id, $inicioSessao);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $jaSalvo);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        $delta = max(0, $totalAtual - (int)$jaSalvo);

        if ($delta > 0) {
            $fim = date('Y-m-d H:i:s');
            $inicioSegmento = date('Y-m-d H:i:s', time() - $delta);

            $stmt2 = mysqli_prepare($conn, "INSERT INTO tempo_uso (maquina_id, inicio, fim, tempo_segundos) 
                                            VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt2, "issi", $id, $inicioSegmento, $fim, $delta);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);
        }

        echo json_encode(['status'=>'ok','delta_salvo'=>$delta]);
        break;

    // ─────────────── FINALIZAR ───────────────
   case 'finalizar':
    // --- Buscar início da sessão ---
    $res = mysqli_query($conn, "SELECT inicio FROM maquinas WHERE id=$id AND status='ocupada'");
    $maq = mysqli_fetch_assoc($res);

    if (!$maq || !$maq['inicio']) {
        echo json_encode(['status'=>'erro','msg'=>'sem sessão ativa']);
        exit;
    }

    $inicioSessao = $maq['inicio'];

    // --- Calcular tempo já salvo ---
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

    // --- Total final de tempo ---
    $stmt3 = mysqli_prepare($conn, "SELECT COALESCE(SUM(tempo_segundos),0) FROM tempo_uso WHERE maquina_id=? AND inicio >= ?");
    mysqli_stmt_bind_param($stmt3, "is", $id, $inicioSessao);
    mysqli_stmt_execute($stmt3);
    mysqli_stmt_bind_result($stmt3, $totalFinal);
    mysqli_stmt_fetch($stmt3);
    mysqli_stmt_close($stmt3);

    // --- Buscar serviços ---
    $serv = $conn->query("SELECT impressoes, scanners FROM servmaq WHERE id_maquina = $id");
    $imp = 0;
    $scn = 0;
    if ($serv && $row = $serv->fetch_assoc()) {
        $imp = (int)$row['impressoes'];
        $scn = (int)$row['scanners'];
    }

    // --- Calcular valores ---
    $meiasHoras = (int)ceil($totalFinal / 1800);
    $valorTempo = $meiasHoras * 2.50;
    $valorImpressoes = $imp * 1.00;
    $valorScanners = $scn > 0 ? 5.00 + ($scn * 1.00) : 0;
    $valorTotal = $valorTempo + $valorImpressoes + $valorScanners;

    // --- Resetar máquina e serviços ---
    mysqli_query($conn, "UPDATE maquinas SET status='livre', inicio=NULL WHERE id=$id");
    mysqli_query($conn, "UPDATE servmaq SET impressoes=0, scanners=0 WHERE id_maquina=$id");

    echo json_encode([
        'status' => 'ok',
        'total_segundos' => (int)$totalFinal,
        'impressoes' => $imp,
        'scanners' => $scn,
        'valor_total' => $valorTotal
    ]);
    break;
}
?>