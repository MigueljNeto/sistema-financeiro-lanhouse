<?php
    require_once __DIR__ . '/../includes/verifica_sessao.php';
    require_once "../includes/servidor.php";

    date_default_timezone_set('America/Sao_Paulo');

    $mesAtual = date('m');
    $anoAtual = date('Y');

    // Função para somar valores do mês
    function somaMes($conn, $tabela, $campo = 'valor', $dataCampo = 'data', $condicaoExtra = '') {
        $sql = "SELECT SUM($campo) AS total FROM $tabela 
                WHERE MONTH($dataCampo) = '$GLOBALS[mesAtual]' 
                AND YEAR($dataCampo) = '$GLOBALS[anoAtual]' $condicaoExtra";
        $resultado = $conn->query($sql);
        if ($resultado && $linha = $resultado->fetch_assoc()) {
            return floatval($linha['total'] ?? 0.00);
        }
        return 0.00;
    }

    // Totais do mês
    $totalServicosMes   = somaMes($conn, 'servicos');
    $depositoMes        = somaMes($conn, 'deposito');
    $pagamentoMes       = somaMes($conn, 'pagamento', 'valor', 'data_hora');
    $valorMaquinasMes   = somaMes($conn, 'servicos', 'valor', 'data', "AND tipo_servico='maquina'");

    // Funcionário destaque do mês
    $resultadoFuncTopMes = $conn->query("
        SELECT usuario, SUM(valor) AS total 
        FROM servicos 
        WHERE MONTH(data) = '$mesAtual' AND YEAR(data) = '$anoAtual'
        GROUP BY usuario 
        ORDER BY total DESC 
        LIMIT 1
    ");
    $funcionarioTopMes = '—';
    if ($resultadoFuncTopMes && $linha = $resultadoFuncTopMes->fetch_assoc()) {
        $funcionarioTopMes = $linha['usuario'] ?? '—';
    }

    // Meta mensal
    $metaMes = 30000.00;

    // Percentuais para barras
    function calculaPercent($valor, $meta) {
        return ($meta > 0) ? round(($valor / $meta) * 100) : 0;
    }

    $percentLucroMes     = calculaPercent($totalServicosMes, $metaMes);
    $percentDepositoMes  = calculaPercent($depositoMes, $metaMes);
    $percentPagamentoMes = calculaPercent($pagamentoMes, $metaMes);
    $percentServicosMes  = calculaPercent($totalServicosMes, $metaMes);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>⚙️ Configurações</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/configuracao.css">
</head>

<body>
    <div class="config-container">
        <h1>⚙️ Configurações da Lan House</h1>
        <div class="cards-config">
            <a href="#metas" class="card-config">
                <div class="icon">🎯</div>
                <h2>Metas</h2>
            </a>
            <a href="#funcionarios" class="card-config">
                <div class="icon">👥</div>
                <h2>Funcionários</h2>
            </a>
            <a href="#servicos" class="card-config">
                <div class="icon">🛠</div>
                <h2>Serviços</h2>
            </a>
        </div>
    </div>

    <div class="dashboard-mes">
        <h1>📅 Resumo Mensal - <?= date('m/Y') ?></h1>

        <div class="indicadores-rapidos">
            <div class="indicador">
                <h3>📊 Serviços do Mês</h3>
                <p>R$ <?= number_format($totalServicosMes, 2, ',', '.') ?></p>
            </div>
            <div class="indicador">
                <h3>🎯 Meta do Mês</h3>
                <p>R$ <?= number_format($metaMes, 2, ',', '.') ?></p>
            </div>
            <div class="indicador">
                <h3>💰 Valor em Máquinas</h3>
                <p>R$ <?= number_format($valorMaquinasMes, 2, ',', '.') ?></p>
            </div>
            <div class="indicador">
                <h3>🏆 Funcionário Destaque</h3>
                <p><?= $funcionarioTopMes ?></p>
            </div>
        </div>

        <div class="cards-resumo">
            <div class="card-mes">
                <h3>📊 Lucro do Mês</h3>
                <p>R$ <?= number_format($totalServicosMes, 2, ',', '.') ?></p>
                <div class="barra-container">
                    <div class="preenchimento lucro" style="width: <?= $percentLucroMes ?>%">
                        <span class="percentual"><?= $percentLucroMes ?>%</span>
                    </div>
                </div>
            </div>

            <div class="card-mes">
                <h3>📈 Depósitos</h3>
                <p>R$ <?= number_format($depositoMes, 2, ',', '.') ?></p>
                <div class="barra-container">
                    <div class="preenchimento deposito" style="width: <?= $percentDepositoMes ?>%">
                        <span class="percentual"><?= $percentDepositoMes ?>%</span>
                    </div>
                </div>
            </div>

            <div class="card-mes">
                <h3>📉 Pagamentos</h3>
                <p>R$ <?= number_format($pagamentoMes, 2, ',', '.') ?></p>
                <div class="barra-container">
                    <div class="preenchimento pagamento" style="width: <?= $percentPagamentoMes ?>%">
                        <span class="percentual"><?= $percentPagamentoMes ?>%</span>
                    </div>
                </div>
            </div>

            <div class="card-mes">
                <h3>🛠 Serviços</h3>
                <p>R$ <?= number_format($totalServicosMes, 2, ',', '.') ?></p>
                <div class="barra-container">
                    <div class="preenchimento servico" style="width: <?= $percentServicosMes ?>%">
                        <span class="percentual"><?= $percentServicosMes ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <canvas id="graficoMes" width="400" height="150"></canvas>
    </div>

    <script>
    const diasMes = Array.from({
        length: new Date(<?= $anoAtual ?>, <?= $mesAtual ?>, 0).getDate()
    }, (_, i) => i + 1);

    const valoresServicos =
        <?php
            $valores = [];
            for($d=1; $d<=date('t'); $d++){
                $dia = str_pad($d,2,'0',STR_PAD_LEFT);
                $sql = "SELECT SUM(valor) as total FROM servicos WHERE DATE(data) = '$anoAtual-$mesAtual-$dia'";
                $res = $conn->query($sql);
                $row = $res->fetch_assoc();
                $valores[] = floatval($row['total'] ?? 0.00);
            }
            echo json_encode($valores);
        ?>;

    const ctx = document.getElementById('graficoMes').getContext('2d');
    const graficoMes = new Chart(ctx, {
        type: 'line',
        data: {
            labels: diasMes,
            datasets: [{
                label: 'Serviços',
                data: valoresServicos,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.2)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>

    <div id="metas" class="card-config">
        <div class="icon">🎯</div>
        <h2>Metas</h2>
        <p>Defina ou altere as metas diárias e mensais.</p>
        <div class="inputsMetas">
            <form action="metas.php" method="post">
                <label for="">Meta do dia em Serviços:</label>
                <input type="number">
                <label for="">Meta do Dia nas Maquinas:</label>
                <input type="number" name="" id="">
                <label for="">Meta do dia dos pagamentos:</label>
                <input type="number">
                <label for="">Meta do Mês</label>
                <input type="number">
            </form>
        </div>
    </div>

    <div id="funcionarios" class="card-config">
        <div class="icon">👥</div>
        <h2>Funcionários</h2>
        <p>Adicione, edite ou remova funcionários do sistema.</p>
        <div class="inputsFuncionarios">
             <form action="funcionarios.php" method="post">
                <label for="">Nome do Funcionario</label>
                <input type="text">
                <label for="">E-mail:</label>
                <input type="email" name="" id="">
                <label for="">Senha:</label>
                <input type="password">
                <label for="">Digite Novamente:</label>
                <input type="password">
            </form>
        </div>
    </div>

    <div id="servicos" class="card-config">
        <div class="icon">🛠</div>
        <h2>Serviços</h2>
        <p>Gerencie os serviços oferecidos e seus valores.</p>
    </div>

</body>

</html>