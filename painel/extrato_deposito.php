<?php
    require_once "../includes/servidor.php";

    
    if (isset($_GET['data_inicio'], $_GET['data_fim']) && $_GET['data_inicio'] !== '' && $_GET['data_fim'] !== '') {
        
        $dataInicio = $_GET['data_inicio'];
        $dataFim = $_GET['data_fim'];

        $stmt = $conn->prepare("SELECT * FROM deposito WHERE DATE(data) BETWEEN ? AND ? ORDER by data DESC");
        $stmt->bind_param("ss", $dataInicio, $dataFim);
        $stmt->execute();

        $result = $stmt->get_result();

    } else {
        
        $sql = "SELECT * FROM deposito ORDER BY data DESC";
        $result = $conn ->query($sql);
    
    }

    
?>

<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>Extrato de Depósitos</title>
        <link rel="stylesheet" href="../css/extrato_deposito.css">

    </head>
    <body>
        <div class= "nav">
            <h1>Extrato de Depósitos</h1>
            <div >
                <form method="GET" action=""  >
                    <label for="data">Pesquisar por data:</label>
                    <input type="date" name="data_inicio" value="<?= isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '' ?>">
                    <label for="data">Até</label>
                    <input type="date" name="data_fim" value="<?= isset($_GET['data_fim']) ? $_GET['data_fim'] : '' ?>">
                    <button type="submit">Buscar</button>
                </form>
            </div>
        </div>

        <table>
            <tr>
                <th>Data</th>
                <th>Valor</th>
                <th>Observação</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($row['data'])) ?></td>
                    <td>R$ <?= number_format($row['valor'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($row['observacao']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>

    </body>
</html>