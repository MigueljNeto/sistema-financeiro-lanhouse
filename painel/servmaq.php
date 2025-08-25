<?php  
session_start();
require_once "../includes/servidor.php";
header('Content-Type: application/json');

$acao = $_POST['acao'] ?? '';
$id_maquina = isset($_POST['id_maquina']) ? (int) $_POST['id_maquina'] : 0;

if ($id_maquina <= 0) {
    echo json_encode(['status' => 'erro', 'msg' => 'Máquina inválida']);
    exit;
}

// --- AÇÃO: ADICIONAR (botões +1 impressão ou +1 scanner) ---
if ($acao === 'adicionar') {
    $tipo       = $_POST['tipo'] ?? '';
    $quantidade = (int) ($_POST['quantidade'] ?? 1);

    if (!in_array($tipo, ['impressoes', 'scanners'])) {
        echo json_encode(['status' => 'erro', 'msg' => 'Tipo inválido']);
        exit;
    }

// verificar se existe registro
$res = $conn->query("SELECT * FROM servmaq WHERE id_maquina = $id_maquina");

if ($res && $res->num_rows > 0) {
    $sql = "UPDATE servmaq SET $tipo = $tipo + $quantidade WHERE id_maquina = $id_maquina";
    $conn->query($sql);
} else {
    $imp = $tipo === 'impressoes' ? $quantidade : 0;
    $scn = $tipo === 'scanners' ? $quantidade : 0;
    $sql = "INSERT INTO servmaq (id_maquina, impressoes, scanners) VALUES ($id_maquina, $imp, $scn)";
    $conn->query($sql);
}
}

// --- AÇÃO: SALVAR (form do modal) ---
elseif ($acao === 'salvar') {
    $impressoes = (int) ($_POST['impressoes'] ?? 0);
    $scanners   = (int) ($_POST['scanners'] ?? 0);

    $res = $conn->query("SELECT * FROM servmaq WHERE id_maquina = $id_maquina");
    if ($res->num_rows > 0) {
        $sql = "UPDATE servmaq 
                   SET impressoes = impressoes + $impressoes,
                       scanners   = scanners + $scanners
                 WHERE id_maquina = $id_maquina";
        $conn->query($sql);
    } else {
        $sql = "INSERT INTO servmaq (id_maquina, impressoes, scanners) 
                VALUES ($id_maquina, $impressoes, $scanners)";
        $conn->query($sql);
    }
}

// --- Sempre retorna os valores atualizados ---
$res = $conn->query("SELECT impressoes, scanners FROM servmaq WHERE id_maquina = $id_maquina");
$data = $res->fetch_assoc();

echo json_encode([
    'status'     => 'ok',
    'impressoes' => (int) $data['impressoes'],
    'scanners'   => (int) $data['scanners']
]);