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

    $hoje = date('Y-m-d');

    // Total de Serviços do Dia
    $totalServicos = 0.00;
    $resultadoServicos = $conn->query("SELECT SUM(valor) AS total FROM servicos WHERE DATE(data) = '$hoje'");
    if ($resultadoServicos && $linha = $resultadoServicos->fetch_assoc()) {
        $totalServicos = $linha['total'] ?? 0.00;
    }

    // Depósitos do Dia
    $depositoDia = 0.00;
    $resultadoDeposito = $conn->query("SELECT SUM(valor) AS total FROM deposito WHERE DATE(data) = '$hoje'");
    if ($resultadoDeposito && $linha = $resultadoDeposito->fetch_assoc()) {
        $depositoDia = $linha['total'] ?? 0.00;
    }

    // Pagamentos do Dia
    $pagamentoDia = 0.00;
    $resultadoPagamento = $conn->query("SELECT SUM(valor) AS total FROM pagamento WHERE DATE(data_hora) = '$hoje'");
    if ($resultadoPagamento && $linha = $resultadoPagamento->fetch_assoc()) {
        $pagamentoDia = $linha['total'] ?? 0.00;
    }

    // Valor em Máquinas (serviços feitos nas máquinas)
    $valorMaquinas = 0.00;
    $resultadoMaquinas = $conn->query("SELECT SUM(valor) AS total FROM servicos WHERE DATE(data) = '$hoje' AND tipo_servico='maquina'");
    if ($resultadoMaquinas && $linha = $resultadoMaquinas->fetch_assoc()) {
        $valorMaquinas = $linha['total'] ?? 0.00;
    }

    // Funcionário Destaque do Dia
    $funcionarioTop = '—';
    $resultadoFuncTop = $conn->query("SELECT usuario, SUM(valor) AS total 
                                    FROM servicos 
                                    WHERE DATE(data) = '$hoje' 
                                    GROUP BY usuario 
                                    ORDER BY total DESC 
                                    LIMIT 1");
    if ($resultadoFuncTop && $linha = $resultadoFuncTop->fetch_assoc()) {
        $funcionarioTop = $linha['usuario'] ?? '—';
    }

    // Meta do Dia
    $metaDia = 1000.00;

    // Percentuais das Barras (evitar divisão por zero)
    $lucroDia = $totalServicos;
    $percentLucro = ($metaDia > 0) ? round(($lucroDia / $metaDia) * 100) : 0;
    $percentDeposito = ($metaDia > 0) ? round(($depositoDia / $metaDia) * 100) : 0;
    $percentPagamento = ($metaDia > 0) ? round(($pagamentoDia / $metaDia) * 100) : 0;
    $percentServicos = ($metaDia > 0) ? round(($totalServicos / $metaDia) * 100) : 0;

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
                    <li><a href="../adm/configuracao.php">⚙️ Configurações</a></li>
                    <li><a href="#">👤 Perfil</a></li>
                    <li><a href="../logout.php">🚪 Sair</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="dashboard">

        <!-- Indicadores Rápidos do Dia -->
        <div class="indicadores-rapidos">
            <div class="indicador">
                <h3>📊 Serviços Hoje</h3>
                <p>R$ <?= number_format($totalServicos, 2, ',', '.') ?></p>
            </div>
            <div class="indicador">
                <h3>🎯 Meta do Dia</h3>
                <p>R$ <?= number_format($metaDia, 2, ',', '.') ?></p>
            </div>
            <div class="indicador">
                <h3>💰 Valor em Máquinas</h3>
                <p>R$ <?= number_format($valorMaquinas, 2, ',', '.') ?></p>
            </div>
            <div class="indicador">
                <h3>🏆 Funcionário Destaque</h3>
                <p><?= $funcionarioTop ?? '—' ?></p>
            </div>
        </div>

        <div class="cards-resumo">
            <div class="card">
                <h3>📊 Lucro do Dia</h3>
                <p>R$ <?= number_format($lucroDia, 2, ',', '.') ?></p>
                <div class="barra-container">
                    <div class="preenchimento lucro" style="width: <?= $percentLucro ?>%">
                        <span class="percentual"><?= $percentLucro ?>%</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>📈 Depósitos</h3>
                <p>R$ <?= number_format($depositoDia, 2, ',', '.') ?></p>
                <div class="barra-container">
                    <div class="preenchimento deposito" style="width: <?= $percentDeposito ?>%">
                        <span class="percentual"><?= $percentDeposito ?>%</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>📉 Pagamentos</h3>
                <p>R$ <?= number_format($pagamentoDia, 2, ',', '.') ?></p>
                <div class="barra-container">
                    <div class="preenchimento pagamento" style="width: <?= $percentPagamento ?>%">
                        <span class="percentual"><?= $percentPagamento ?>%</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>🛠 Serviços</h3>
                <p>R$ <?= number_format($totalServicos, 2, ',', '.') ?></p>
                <div class="barra-container">
                    <div class="preenchimento servico" style="width: <?= $percentServicos ?>%">
                        <span class="percentual"><?= $percentServicos ?>%</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="conteiner_aba">
        <div class="abas">
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
                            <p>
                                <button class="btn-remove" onclick="removerServico('.$id.', \'impressoes\')">-</button>
                                Impressões: <span id="impressoes_'.$id.'">'.$impressoes.'</span>
                                <button class="btn-add" onclick="adicionarServico('.$id.', \'impressoes\')">+</button>
                            </p>
                            <p>
                                <button class="btn-remove" onclick="removerServico('.$id.', \'scanners\')">-</button>
                                Scanners: <span id="scanners_'.$id.'">'.$scanners.'</span>
                                <button class="btn-add" onclick="adicionarServico('.$id.', \'scanners\')">+</button>
                            </p>
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