<?php
require_once __DIR__ . '/../includes/verifica_sessao.php';
require_once "../includes/servidor.php";

$sql = "SELECT SUM(valor) AS total FROM deposito";
$resultado = $conn->query($sql);

$totalDeposito = 0.00;

if ($resultado && $linha = $resultado->fetch_assoc()) {
    $totalDeposito = $linha['total'] ?? 0.00;
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <title>Pagina Inicial</title>
    <link rel="stylesheet" href="../css/inicio.css">
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
                <h1>R$: 0,00</h1>
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
                <h1>R$: 0,00</h1>
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
                <select name="formas" id="formas">
                    <option value="">Forma de Pagamento</option>
                    <option value="pix">Pix</option>
                    <option value="dinheiro">Dinheiro</option>
                </select>
                <label for="">Valor:</label>
                <input type="number" name="valor" placeholder="VALOR" required>
                <label for="">Observação:</label>
                <input type="text" name="observacao" placeholder="OBSERVAÇÃO">
                <button type="submit">ENVIAR</button>
            </form>
        </div>
    </div>



    <div class="conteiner_contador">

        <div class="contador">
            <h2><img src="../imagens/465.png" alt=""></h2>
            <h1>MAQUINA 1</h1>

            <div class="relogio">
                <h1 id="idDoRelogio">00:00</h1>
            </div>

            <div>
                <button id="btnIniciar1"
                    onclick="iniciarContador('idDoRelogio', 'btnIniciar1', 'btnParar1')">INICIAR</button>
                <button id="btnParar1" style="display: none;" onclick="pararContador()">PARAR</button>

            </div>
            <div>
                <button onclick=" finalizarmaquina('idDoRelogio, BtnIniciar1, btnParar1')">FINALIZAR</button>
            </div>
            <div>
                <button onclick="abrirModal('modal-relogio1')">VERIFICAR SERVIÇOS</button>
            </div>

        </div>

        <div class="contador">
            <h2><img src="../imagens/465.png" alt=""></h2>
            <h1>MAQUINA 2</h1>

            <div class="relogio">
                <h1 id="idDoRelogio">00:00</h1>
            </div>

            <div>
                <button id="btnIniciar1"
                    onclick="iniciarContador('idDoRelogio', 'btnIniciar1', 'btnParar1')">INICIAR</button>
                <button id="btnParar1" style="display: none;" onclick="pararContador()">PARAR</button>

            </div>
            <div>
                <button onclick=" finalizarmaquina('idDoRelogio, BtnIniciar1, btnParar1')">FINALIZAR</button>
            </div>
            <div>
                <button onclick="abrirModal('modal-relogio1')">VERIFICAR SERVIÇOS</button>
            </div>

        </div>

        <div class="contador">
            <h2><img src="../imagens/465.png" alt=""></h2>
            <h1>MAQUINA 3</h1>

            <div class="relogio">
                <h1 id="idDoRelogio">00:00</h1>
            </div>

            <div>
                <button id="btnIniciar1"
                    onclick="iniciarContador('idDoRelogio', 'btnIniciar1', 'btnParar1')">INICIAR</button>
                <button id="btnParar1" style="display: none;" onclick="pararContador()">PARAR</button>

            </div>
            <div>
                <button onclick=" finalizarmaquina('idDoRelogio, BtnIniciar1, btnParar1')">FINALIZAR</button>
            </div>
            <div>
                <button onclick="abrirModal('modal-relogio1')">VERIFICAR SERVIÇOS</button>
            </div>

        </div>


        <div class="contador">
            <h2><img src="../imagens/465.png" alt=""></h2>
            <h1>MAQUINA 4</h1>

            <div class="relogio">
                <h1 id="idDoRelogio">00:00</h1>
            </div>

            <div>
                <button id="btnIniciar1"
                    onclick="iniciarContador('idDoRelogio', 'btnIniciar1', 'btnParar1')">INICIAR</button>
                <button id="btnParar1" style="display: none;" onclick="pararContador()">PARAR</button>

            </div>
            <div>
                <button onclick=" finalizarmaquina('idDoRelogio, BtnIniciar1, btnParar1')">FINALIZAR</button>
            </div>
            <div>
                <button onclick="abrirModal('modal-relogio1')">VERIFICAR SERVIÇOS</button>
            </div>

        </div>
        <!-- <?php
        for ($contador = 0; $contador <= 3; $contador++) {
            ?>          
        <?php
        }
        ?>  -->

    </div>
</body>

</html>