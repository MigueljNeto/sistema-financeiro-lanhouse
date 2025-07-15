<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <title>Pagina Inicial</title>
  <link rel="stylesheet" href="../css/index.css">
</head>

<body>
  <div class="conteiner-central">
    <div class="logo">
      <img src="../imagens/logo.png" alt="logo">
    </div>

    <div class="abas-nav">
      <button onclick="mostrarAba('login')">Login</button>
      <button onclick="mostrarAba('cadastro')">Cadastrar</button>

    </div>


    <div id="login" class="aba ativa">
      <form action="processa_login.php" method="post">
        <input type="text" name="email" placeholder="EMAIL" required>
        <input type="password" name="senha" placeholder="SENHA" required>
        <button type="submit">ENTRAR</button>
      </form>
    </div>


    <div id="cadastro" class="aba">
      <form action="cadastrar.php" method="post">
        <input type="text" name="email" placeholder="NOVO EMAIL" required>
        <input type="password" name="senha" placeholder="SENHA" required>
        <input type="password" name="senhaadm" placeholder="SENHA DO ADM" required>
        <button type="submit">CADASTRAR</button>
      </form>
    </div>

    <script>
      function mostrarAba(id) {
        const abas = document.querySelectorAll('.aba');
        abas.forEach(aba => aba.classList.remove('ativa'));
        document.getElementById(id).classList.add('ativa');
      }
    </script>
</body>

</html>