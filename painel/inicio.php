<?php
    require_once __DIR__ . '/../includes/verifica_sessao.php';
    require_once "../includes/servidor.php";
    
    date_default_timezone_set('America/Sao_Paulo');

    $hoje = date('Y-m-d');

    $sql = "SELECT SUM(valor) AS total 
            FROM deposito 
            WHERE DATE(data) = '$hoje'";
    $resultado = $conn->query($sql);

    $totalDeposito = 0.00;
    if ($resultado && $linha = $resultado->fetch_assoc()) {
        $totalDeposito = $linha['total'] ?? 0.00;
    }

    $sql = "SELECT SUM(valor) AS total_pago 
            FROM pagamento 
            WHERE DATE(data_hora) = '$hoje'";
    $result = $conn->query($sql);

    $total_pago = 0.00;
    if($result){
        $row = $result->fetch_assoc();
        $total_pago = $row['total_pago'] ?? 0.00;
    }

    $sql = "SELECT SUM(valor) as total 
            FROM servicos 
            WHERE DATE(data) = '$hoje'";
    $result = $conn->query($sql);

    $total = 0.00;
    if ($result && $row = $result->fetch_assoc()) {
        $total = $row['total'] ?? 0.00;
    }

    $mesAtual = date('m');
    $anoAtual = date('Y');

    $sql = "SELECT SUM(valor) AS total FROM deposito 
            WHERE MONTH(data) = '$mesAtual' AND YEAR(data) = '$anoAtual'";
    $resultado = $conn->query($sql);

    $deposito = 0.00;
    if ($resultado && $linha = $resultado->fetch_assoc()) {
        $deposito = $linha['total'] ?? 0.00;
    }

    $result = $conn->query("SELECT SUM(valor) AS total_pago FROM pagamento 
                            WHERE MONTH(data_hora) = '$mesAtual' AND YEAR(data_hora) = '$anoAtual'");
    $pagamento = 0.00;
    if($result){
        $row = $result->fetch_assoc();
        $pagamento = $row['total_pago'] ?? 0.00;
    }
    $mesAnoAtual = date('Y-m'); // Exemplo: "2025-09"

    $sqlServicosMes = "SELECT IFNULL(SUM(valor), 0) AS totalServicosMes 
                    FROM servicos 
                    WHERE DATE_FORMAT(data, '%Y-%m') = '$mesAnoAtual'";
    $resServMes = $conn->query($sqlServicosMes);
    $servicosMes = $resServMes->fetch_assoc()['totalServicosMes'];

    $lucro = $servicosMes;

    $sql = "SELECT SUM(valor) as total FROM servicos 
            WHERE MONTH(data) = '$mesAtual' AND YEAR(data) = '$anoAtual'";
    $result = $conn->query($sql);

    $total = 0.00;
    if ($result && $row = $result->fetch_assoc()) {
        $total = $row['total'] ?? 0;
    }

    $sqlFunc = "SELECT usuario, SUM(valor) as total 
                FROM servicos 
                WHERE MONTH(data) = '$mesAtual' AND YEAR(data) = '$anoAtual'
                GROUP BY usuario";
    $resFunc = $conn->query($sqlFunc);

    $usuario = [];
    $valoresUsuarios = [];
    while ($row = $resFunc -> fetch_assoc()){
        $usuario[] = $row['usuario'];
        $valoresUsuarios[] = $row['total'];
    }

    $sqlPag = "SELECT tipo_pagamento, SUM(valor) as total 
               FROM servicos 
               WHERE MONTH(data) = '$mesAtual' AND YEAR(data) = '$anoAtual'
               GROUP BY tipo_pagamento";
    $resPag = $conn->query($sqlPag);

    $formas = [];
    $valoresPag = [];
    while ($row = $resPag -> fetch_assoc()){
        $formas[] = $row['tipo_pagamento'];
        $valoresPag[] = $row['total'];
    }
    // Meta do mês (exemplo, você pode ajustar)
    $metaMes = 5000.00;

    $sqlServicos = "SELECT SUM(valor) AS total 
    FROM servicos
    WHERE DATE(data) = '$hoje'";
    $resultadoServicos = $conn->query($sqlServicos);

    $totalServicos = 0.00;
    if ($resultadoServicos && $linhaServicos = $resultadoServicos->fetch_assoc()) {
        $totalServicos = $linhaServicos['total'] ?? 0.00;
    }

    $percentLucro = ($lucro > 0 && $metaMes > 0) ? round(($lucro / $metaMes) * 100) : 0;
    $percentDeposito = ($deposito > 0 && $metaMes > 0) ? round(($deposito / $metaMes) * 100) : 0;
    $percentPagamento = ($pagamento > 0 && $metaMes > 0) ? round(($pagamento / $metaMes) * 100) : 0;
    $percentServicos = ($totalServicos > 0 && $metaMes > 0) ? round(($totalServicos / $metaMes) * 100) : 0;

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <title>Pagina Inicial</title>
    <link rel="stylesheet" href="../css/inicio.css">
    <link rel="stylesheet" href="../css/modal.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="inicio.js"></script>

</head>

<body>

    <div class="nav full">
        <h1>LAN HOUSE ISNARA - FINANCEIRO</h1>

        <div class="nav-right">
            <div class="datetime">
                📅 <?php echo date('d/m/Y'); ?> ⏰ <?php echo date('H:i'); ?>
            </div>

            <div class="user-menu">
                <div class="user-trigger">
                    <img class="avatar" src="../imagens/user.png" alt="user">
                    <span><?php echo $_SESSION['usuario']; ?></span>
                    <img class="arrow" src="../imagens/seta-baixo.png" alt="seta">
                </div>
                <ul class="dropdown">
                    <li><a href="#">⚙️ Configurações</a></li>
                    <li><a href="#">👤 Perfil</a></li>
                    <li><a href="../logout.php">🚪 Sair</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="dashboard">

        <div class="indicadores-rapidos">
            <div class="indicador">
                <h3>💻 Máquinas Ativas</h3>
                <p><?= $maquinasAtivas ?? 0 ?></p>
            </div>
            <div class="indicador">
                <h3>📊 Serviços Hoje</h3>
                <p>R$ <?php echo number_format($totalServicos, 2, ',', '.'); ?></p>
            </div>
            <div class="indicador">
                <h3>🎯 Meta do Mês</h3>
                <p>R$ <?= number_format($metaMes,2,',','.') ?></p>
            </div>
        </div>

        <div class="cards-resumo">
            <div class="card">
                <h3>📊 Lucro da Lan House (Mês)</h3>
                <p>R$ <?= number_format($lucro,2,',','.') ?></p>
                <div class="barra-container">
                    <div class="preenchimento lucro" style="width: <?= $percentLucro ?>%">
                        <span class="percentual"><?= $percentLucro ?>%</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>📈 Depósitos (Mês)</h3>
                <p>R$ <?= number_format($deposito,2,',','.') ?></p>
                <div class="barra-container">
                    <div class="preenchimento deposito" style="width: <?= $percentDeposito ?>%">
                        <span class="percentual"><?= $percentDeposito ?>%</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>📉 Pagamentos (Mês)</h3>
                <p>R$ <?= number_format($pagamento,2,',','.') ?></p>
                <div class="barra-container">
                    <div class="preenchimento pagamento" style="width: <?= $percentPagamento ?>%">
                        <span class="percentual"><?= $percentPagamento ?>%</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>🛠 Serviços (Mês)</h3>
                <p>R$ <?= number_format($totalServicos,2,',','.') ?></p>
                <div class="barra-container">
                    <div class="preenchimento servico" style="width: <?= $percentServicos ?>%">
                        <span class="percentual"><?= $percentServicos ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grafico">
            <h2>Total por Funcionário (Mês)</h2>
            <canvas id="graficoFuncionario"></canvas>
        </div>

        <div class="grafico">
            <h2>Total por Forma de Pagamento (Mês)</h2>
            <canvas id="graficoPagamento"></canvas>
        </div>

    </div>

    <script>
    new Chart(document.getElementById('graficoFuncionario'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($usuario) ?>,
            datasets: [{
                label: 'Total (R$)',
                data: <?= json_encode($valoresUsuarios) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    new Chart(document.getElementById('graficoPagamento'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($formas) ?>,
            datasets: [{
                label: 'Total (R$)',
                data: <?= json_encode($valoresPag) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>

    <div class="conteiner_aba">
        <div class="abas">
            <div class="barra-container">
                <div class="preenchimento deposito" data-meta="10000" data-valor="<?php echo $totalDeposito; ?>">
                    <span class="percentual"></span>
                </div>
            </div>
            <h1>
                DEPOSITO
            </h1>
            <div class="valor">
                <h1>R$: <?php echo number_format($totalDeposito, 2, ',', '.'); ?></h1>
            </div>

            <div>
                <button onclick="abrirModal('modal-deposito')">REGISTRAR DEPOSITO</button>
            </div>
            <div>
                <a href="extrato_deposito.php">EXTRATO</a>
            </div>
        </div>


        <div class="abas">
            <div class="barra-container">
                <div class="preenchimento pagamento" data-meta="10000" data-valor="<?php echo $total_pago; ?>">
                    <span class="percentual"></span>
                </div>
            </div>
            <h1>
                CONTAS PAGAS
            </h1>
            <div class="valor">
                <h1>R$: <?php echo number_format($total_pago, 2, ',', '.'); ?></h1>
            </div>
            <div>
                <button onclick="abrirModal('modal-pagamento')">REGISTRAR PAGAMENTO</button>
            </div>
            <div>
                <a href="extrato_pagamento.php">EXTRATO</a>
            </div>
        </div>

        <div class="abas">
            <div class="barra-container">
                <div class="preenchimento servico" data-meta="2000" data-valor="<?php echo $total; ?>">
                    <span class="percentual"></span>
                </div>
            </div>
            <h1>
                SERVIÇOS
            </h1>
            <div class="valor">
                <h1>R$: <?php echo number_format($total, 2, ',', '.'); ?></h1>
            </div>
            <div>
                <button onclick="abrirModal('modal-servico')">REGISTRAR SERVIÇOS</button>
            </div>
            <div>
                <a href="extrato_servico.php">EXTRATO</a>
            </div>
        </div>
    </div>

    <div id="modal-deposito" class="fundo-modal">
        <div class="caixa-modal">
            <button class="botao-fechar" onclick="fecharModal('modal-deposito')">X</button>
            <h2>Registrar Depósito</h2>
            <form action="deposito.php" method="post">
                <label for="">Valor:</label>
                <input type="number" name="valor" step="0.01" placeholder="Ex: 12.50" required>
                <label for="">Observação</label>
                <input type="text" name="observacao" placeholder="OBSERVAÇÃO">
                <button type="submit">ENVIAR</button>
            </form>
        </div>
    </div>

    <div id="modal-pagamento" class="fundo-modal">
        <div class="caixa-modal">
            <button class="botao-fechar" onclick="fecharModal('modal-pagamento')">X</button>
            <h2>Registrar Pagamento</h2>
            <form action="pagamento.php" method="post">
                <select name="tipos" id="tipos">
                    <option value="">FORMA DE PAGAMENTO</option>
                    <option value="pix">PIX</option>
                    <option value="dinheiro">DINHEIRO</option>
                    <option value="cartao">CARTÃO</option>
                </select>
                <label for="">Valor:</label>
                <input type="number" step="0.01" name="valor" placeholder="Ex: 12.34" required>
                <label for="">Observação</label>
                <input type="text" name="observacao" placeholder="OBSERVAÇÃO">
                <button type="submit">ENVIAR</button>
            </form>
        </div>
    </div>

    <div id="modal-servico" class="fundo-modal">
        <div class="caixa-modal">
            <button class="botao-fechar" onclick="fecharModal('modal-servico')">X</button>
            <h2>Registrar Serviço</h2>
            <form action="servico.php" method="post">
                <select name="usuarios" id="usuarios">
                    <option value="">Funcionario</option>
                    <option value="Miguel">Miguel</option>
                    <option value="Romulo">Romulo</option>
                    <option value="Bobbie Goods">Bobbie Goods</option>
                </select>
                <select name="formas" id="formas">
                    <option value="">Forma de Pagamento</option>
                    <option value="pix">Pix</option>
                    <option value="dinheiro">Dinheiro</option>
                </select>
                <label for="">Valor:</label>
                <input type="number" name="valor" step="0.01" placeholder="VALOR" required>
                <label for="">Observação:</label>
                <input type="text" name="observacao" placeholder="OBSERVAÇÃO">
                <button type="submit">ENVIAR</button>
            </form>
        </div>
    </div>

    <div class="conteiner_contador">

        <div class="add">
            <button onclick="abrirModal('modal-maquina')">
                <h1>+</h1>
            </button>
        </div>

        <?php
            $resultado = mysqli_query($conn, "SELECT * FROM maquinas");

            while ($maquina = mysqli_fetch_assoc($resultado)) {
                $id = $maquina['id']; 
                $status = $maquina['status'];          
                $inicio = $maquina['inicio'];          

                $elapsed = 0;
                
                if ($status === 'ocupada' && $inicio) {
                    $stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(tempo_segundos),0) FROM tempo_uso WHERE maquina_id = ? AND inicio >= ?");
                    mysqli_stmt_bind_param($stmt, "is", $id, $inicio);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $salvo);
                    mysqli_stmt_fetch($stmt);
                    mysqli_stmt_close($stmt);

                    $elapsed = (int)$salvo + (time() - strtotime($inicio)); // segundos
                }

                $serv = $conn->query("SELECT impressoes, scanners FROM servmaq WHERE id_maquina = {$maquina['id']}");
                $impressoes = 0;
                $scanners = 0;
                
                if ($serv && $row = $serv->fetch_assoc()) {
                    $impressoes = (int)$row['impressoes'];
                    $scanners = (int)$row['scanners'];
                }
                
                echo '
                    <div class="contador" data-id="'.$id.'" data-status="'.$status.'" data-elapsed="'.$elapsed.'">
                        
                        <h2><img src="../imagens/465.png" alt=""></h2>
                        <h1>' . strtoupper(htmlspecialchars($maquina['nome'])) . '</h1>

                        <div class="relogio">
                            <h1 id="relogio_' . $maquina['id'] . '">00:00:00</h1>
                        </div>
                        
                        <div class="resumo-servicos">
                            <p>Impressões: <span id="impressoes_'.$id.'">'.$impressoes.'</span></p>
                            <p>Scanners: <span id="scanners_'.$id.'">'.$scanners.'</span></p>
                        </div>

                        <div class="atalhos-servicos">
                            <button onclick="adicionarServico('.$id.', \'impressoes\')">+ Impressão</button>
                            <button onclick="removerServico('.$id.', \'impressoes\')">- Impressão</button>

                            <button onclick="adicionarServico('.$id.', \'scanners\')">+ Scanner</button>
                            <button onclick="removerServico('.$id.', \'scanners\')">- Scanner</button>
                        </div>

                        <div>
                            <button id="btnIniciar_' . $id . '" onclick="iniciarContador(' . $id . ')">INICIAR</button>
                            <button id="btnParar_' . $id . '" style="display:none;" onclick="pararContador(' . $id . ')">PARAR</button>
                        </div>

                        <div>
                            <button onclick="finalizarMaquina(' . $id . ')">FINALIZAR</button>
                        </div>

                        <div>
                            <button onclick="abrirModalMaquina(' . $maquina['id'] . ')">VERIFICAR SERVIÇOS</button>
                        </div>

                        <div>
                            <button class="apagar" onclick="abrirModalExcluir(' . $maquina['id'] . ')">EXCLUIR</button>
                        </div>
                    </div>

                    
                    <div id="modalMaquina_'.$id.'" class="modalMaqServ" style="display:none;">
                        <div class="modalMaq">
                            <button class="botao-fechar" onclick="fecharModalMaquina('.$id.')">X</button>
                            <h3>Serviços - '.strtoupper(htmlspecialchars($maquina['nome'])).'</h3>

                            <form id="form_servicos_'.$id.'" onsubmit="salvarServicos(event, '.$id.')">
                                <label>Impressões:</label>
                                <input type="number" name="impressoes" min="0" value="0"><br>

                                <label>Scanners:</label>
                                <input type="number" name="scanners" min="0" value="0"><br>

                                <button type="submit">Salvar Serviços</button>
                            </form>

                            <div id="servicos_lista_'.$id.'"></div>
                        </div>
                    </div>
                ';
            }
        ?>
    </div>

    <div id="modal-maquina" class="fundo-modal">
        <div class="caixa-modal-maq">
            <button class="botao-fechar" onclick="fecharModal('modal-maquina')">X</button>
            <h2>Registrar Depósito</h2>
            <form action="maquina.php" method="post">
                <label for="">Nome da Maquina:</label>
                <input type="text" name="maquina" required>
                <button type="submit">ENVIAR</button>
            </form>
        </div>
    </div>
</body>

</html>