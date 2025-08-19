<?php
require_once "../includes/servidor.php";

if (isset($_GET['data_inicio'], $_GET['data_fim']) && $_GET['data_inicio'] !== '' && $_GET['data_fim'] !== '') {

$dataInicio = $_GET['data_inicio'];
$dataFim = $_GET['data_fim'];

$stmt = $conn->prepare("SELECT * FROM pagamento WHERE DATE(data_hora) BETWEEN ? AND ? ORDER BY data_hora DESC");
$stmt->bind_param("ss", $dataInicio, $dataFim);
$stmt->execute();
$result = $stmt->get_result();

} else {

$sql = "SELECT * FROM pagamento ORDER BY data_hora DESC";
$result = $conn->query($sql);

}
?>

<!DOCTYPE html>
<html lang="pt-br">

    <head>
        <meta charset="UTF-8">
        <title>Extrato dos Pagamentos</title>
        <link rel="stylesheet" href="../css/extrato_deposito.css">
    </head>

    <body>
        <div class="nav">
            <h1>Extrato de Pagamentos</h1>
            <div>
                <form method="GET" action="">
                    <label for="data_inicio">Pesquisar por data:</label>
                    <input type="date" name="data_inicio"
                        value="<?= isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '' ?>">
                    <label for="data_fim">Até</label>
                    <input type="date" name="data_fim" value="<?= isset($_GET['data_fim']) ? $_GET['data_fim'] : '' ?>">
                    <button type="submit">Buscar</button>
                </form>
            </div>
        </div>

        <table>
            <tr>
                <th>Data</th>
                <th>Valor</th>
                <th>Tipo do Pagamento</th>
                <th>Observação</th>
            </tr>

            <?php 
            $total = 0;
            while ($row = $result->fetch_assoc()) : 
                $total += $row['valor'];
            ?>
            <tr>
                <td><?= date('d/m/Y H:i', strtotime($row['data_hora'])) ?></td>
                <td>R$ <?= number_format($row['valor'], 2, ',', '.') ?></td>
                <td><?= htmlspecialchars($row['tipo_pagamento']) ?></td>
                <td><?= htmlspecialchars($row['observacao']) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

    </body>

</html>