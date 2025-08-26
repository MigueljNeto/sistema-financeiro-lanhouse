<?php
    require_once __DIR__ . '/../includes/verifica_sessao.php';
    require_once "../includes/servidor.php";

    //Somando os Valores do Deposito
    $sql = "SELECT SUM(valor) AS total FROM deposito";
    $resultado = $conn->query($sql);

    $totalDeposito = 0.00;

    if ($resultado && $linha = $resultado->fetch_assoc()) {
        $totalDeposito = $linha['total'] ?? 0.00;
    }

    //Somando os Valores dos Pagamentos    
    $result = $conn->query("SELECT SUM(valor) AS total_pago FROM pagamento");

    $total_pago = 0;
    if($result){
        $row = $result->fetch_assoc();
        $total_pago = $row['total_pago'] ?? 0;
    }

    //Somando os Valores dos Serviços
    $sql = "SELECT SUM(valor) as total FROM servicos";
    $result = $conn->query($sql);

    $total = 0.00;

    if ($result && $row = $result->fetch_assoc()) {
        $total = $row['total'] ?? 0;
    }

    //Buscando os Valores pra inserir nos graficos
    $sqlFunc = "SELECT usuario, SUM(valor) as total FROM servicos GROUP BY usuario";
    $resFunc = $conn->query($sqlFunc);

    $usuario = [];
    $valoresUsuarios = [];

    while ($row = $resFunc -> fetch_assoc()){
        $usuario[] = $row['usuario'];
        $valoresUsuarios[] = $row['total'];
    }

    $sqlPag = "SELECT tipo_pagamento, SUM(valor) as total FROM servicos GROUP BY tipo_pagamento";
    $resPag = $conn->query($sqlPag);

    $formas = [];
    $valoresPag = [];

    while ($row = $resPag -> fetch_assoc()){
        $formas[] = $row['tipo_pagamento'];
        $valoresPag[] = $row['total'];
    }

    $hoje = date('Y-m-d');
    
    $sqlDeposito = "SELECT IFNULL(SUM(valor), 0) AS totalDeposito 
                    FROM deposito 
                    WHERE DATE(data) = '$hoje'";
    $resDep = $conn->query($sqlDeposito);
    $deposito = $resDep->fetch_assoc()['totalDeposito'];

    $sqlPagamento = "SELECT IFNULL(SUM(valor), 0) AS totalPagamento 
                    FROM pagamento 
                    WHERE DATE(data_hora) = '$hoje'";
    $resPag = $conn->query($sqlPagamento);
    $pagamento = $resPag->fetch_assoc()['totalPagamento'];

    $saldoDia = $deposito - $pagamento;

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

    <div class="nav">
        <div>
            <h1>LAN HOUSE ISNARA - FINANCEIRO</h1>
        </div>
        <div>
            <h2>
                <img src="../imagens/user.png" alt="user">
                <?php
                    echo $_SESSION['usuario'];
                    ?>
                <img src="../imagens/seta-baixo.png" alt="seta">
            </h2>
        </div>
    </div>
    
    <div class="conteiner_principal">
        <div class="graficoFunc">
            <h2>Total por Funcionário</h2>
            <canvas id="graficoFuncionario"></canvas>
        </div>
        
        <div class="graficoSald">
            <div>
                <h2>Saldo disponível hoje: <br>R$ <?php echo number_format($saldoDia, 2, ',', '.'); ?></h2>
            </div>
            <canvas id="graficoDia"></canvas>
        </div>    
        
        <div class="graficoPag">
            <h2>Total por Forma de Pagamento</h2>
            <canvas id="graficoPagamento"></canvas>
        </div>

    </div>

    <script>

        const ctxFunc = document.getElementById('graficoFuncionario').getContext('2d');
        new Chart(ctxFunc, {
            type: 'bar',
            data: {
                labels: <?= json_encode($usuario) ?>,
                datasets: [{
                    label: 'Total (R$)',
                    data: <?= json_encode($valoresUsuarios) ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',  // vermelho
                        'rgba(54, 162, 235, 0.6)',  // azul
                        'rgba(255, 206, 86, 0.6)'   // amarelo
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)'
                    ],
                    borderWidth: 1,
                    borderRadius: 8 // borda arredondada das barras
                }]
            },
            options: {
                responsive: true,
                plugins: { 
                    legend: { display: false },
                    tooltip: { 
                        callbacks: {
                            label: ctx => `R$ ${ctx.raw.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

            const ctxPag = document.getElementById('graficoPagamento').getContext('2d');
            new Chart(ctxPag, {
                type: 'pie',
                data: {
                    labels: <?= json_encode($formas) ?>,
                    datasets: [{
                        data: <?= json_encode($valoresPag) ?>,
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.6)',  // verde água
                            'rgba(255, 159, 64, 0.6)',  // laranja
                            'rgba(255, 99, 132, 0.6)',  // vermelho (se houver mais)
                            'rgba(54, 162, 235, 0.6)'   // azul (se houver mais)
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { font: { size: 14 } }
                        },
                        tooltip: { 
                            callbacks: {
                                label: ctx => `R$ ${ctx.raw.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`
                            }
                        }
                    }
                }
            });

            const ctx = document.getElementById('graficoDia').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Depósitos', 'Pagamentos', 'Saldo'],
                    datasets: [{
                        label: 'Valores do dia',
                        data: [<?php echo $deposito; ?>, <?php echo $pagamento; ?>, <?php echo $saldoDia; ?>],
                        backgroundColor: [
                            'rgba(46, 204, 113, 0.85)',   // verde elegante
                            'rgba(231, 76, 60, 0.85)',    // vermelho elegante
                            'rgba(52, 152, 219, 0.85)'    // azul elegante
                        ],
                        borderColor: '#fff',
                        borderWidth: 2,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { size: 14 },
                                color: '#333' 
                        },
                        tooltip: { 
                            callbacks: {
                                label: ctx => `R$ ${ctx.raw.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`
                            }
                        }
                    }
                }
            });
    </script>

    <div class="conteiner_aba">
        <div class="abas">
            <h1>
                DEPOSITO
            </h1>
            <div class="valor">
                <h1>
                    R$: <?php echo number_format($totalDeposito, 2, ',', '.'); ?>
                </h1>
            </div>
            <div>
                <button onclick="abrirModal('modal-deposito')">REGISTRAR DEPOSITO</button>
            </div>
            <div>
                <a href="extrato_deposito.php">EXTRATO</a>
            </div>
        </div>

        <div class="abas">
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
            <h2>Registrar Depósito</h2>
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
            <h2>Registrar Depósito</h2>
            <form action="servico.php" method="post">
                <select name="usuarios" id="usuarios">
                    <option value="">Funcionario</option>
                    <option value="miguel">Miguel</option>
                    <option value="romulo">Romulo</option>
                    <option value="bobgudes">Bobbie Goods</option>
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
                        
                        <!-- Resumo dos serviços -->
                        <div class="resumo-servicos">
                            <p>Impressões: <span id="impressoes_'.$id.'">'.$impressoes.'</span></p>
                            <p>Scanners: <span id="scanners_'.$id.'">'.$scanners.'</span></p>
                        </div>

                         <!-- Atalhos rápidos -->
                        <div class="atalhos-servicos">
                            <button onclick="adicionarServico('.$id.', \'impressoes\')">+ Impressão</button>
                            <button onclick="adicionarServico('.$id.', \'scanners\')">+ Scanner</button>
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