<?php
    require_once __DIR__ . '/../includes/verifica_sessao.php';
    require_once "../includes/servidor.php";

    $sql = "SELECT SUM(valor) AS total FROM deposito";
    $resultado = $conn->query($sql);

    $totalDeposito = 0.00;

    if ($resultado && $linha = $resultado->fetch_assoc()) {
        $totalDeposito = $linha['total'] ?? 0.00;
    }

    
    $result = $conn->query("SELECT SUM(valor) AS total_pago FROM pagamento");

    $total_pago = 0;
    if($result){
        $row = $result->fetch_assoc();
        $total_pago = $row['total_pago'] ?? 0;
    }

    $sql = "SELECT SUM(valor) as total FROM servicos";
    $result = $conn->query($sql);

    $total = 0.00;

    if ($result && $row = $result->fetch_assoc()) {
        $total = $row['total'] ?? 0;
    }
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <title>Pagina Inicial</title>
    <link rel="stylesheet" href="../css/inicio.css">
    <link rel="stylesheet" href="../css/modal.css">
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
        <div>

        </div>
    </div>
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
                
                echo '
                    
                    <div class="contador" data-id="'.$id.'" data-status="'.$status.'" data-elapsed="'.$elapsed.'" >
                        
                        <h2><img src="../imagens/465.png" alt=""></h2>
                        <h1>' . strtoupper(htmlspecialchars($maquina['nome'])) . '</h1>

                        <div class="relogio">
                            <h1 id="relogio_' . $maquina['id'] . '">00:00:00</h1>
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

                    <div id="modalExcluir" class="modal" style="display:none;">
                        <div class="modal-conteudo">
                            
                            <button class="botao-fechar" onclick="fecharModalExcluir()">X</button>

                            <h2>Confirmar Exclusão</h2>
                            <p>Tem certeza que deseja excluir esta máquina?</p>

                            <form id="formExcluir" action="maquina.php" method="post">
                                <input type="hidden" name="id" id="idExcluir">
                                <button type="submit">Sim, excluir</button>
                                <button type="button" onclick="fecharModalExcluir()">Cancelar</button>
                            </form>

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