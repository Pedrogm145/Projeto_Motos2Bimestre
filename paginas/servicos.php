<?php
    require_once __DIR__ . '/../conexao/conexao.php';
    session_start();

    if (!isset($_SESSION['id'])) {
        header('Location: login.php');
        exit();
    }
    
    // Verificar se usuário está logado e é admin
    $usuarioLogado = isset($_SESSION['nome']);
    $nomeUsuario = $usuarioLogado ? $_SESSION['nome'] : '';
    $isAdmin = false;
    
    if (isset($_SESSION['id'])) {
        $sqlVerificaAdmin = "SELECT is_admin FROM users WHERE id = ?";
        $stmtAdmin = $conn->prepare($sqlVerificaAdmin);
        $stmtAdmin->bind_param("i", $_SESSION['id']);
        $stmtAdmin->execute();
        $resultAdmin = $stmtAdmin->get_result();
        $usuarioAdmin = $resultAdmin->fetch_assoc();
        $stmtAdmin->close();
        $isAdmin = $usuarioAdmin && $usuarioAdmin['is_admin'] == 1;
    }
    
    // Buscar serviços ativos
    $sqlServicos = "SELECT * FROM servicos WHERE ativo = 1 ORDER BY nome ASC";
    $resultServicos = $conn->query($sqlServicos);
    $servicos = [];
    while ($row = $resultServicos->fetch_assoc()) {
        $servicos[] = $row;
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviços - Thunder Motors</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/menu_admin.css">
    <link rel="stylesheet" href="../assets/css/servicos.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-motorcycle"></i>
                    <span>Thunder Motors</span>
                </div>
                <div id="teste">
                    <nav id="nav-menu">
                        <ul>
                            <li><a href="../index.php#servicos">Serviços</a></li>
                            <li><a href="#portfolio">Portfólio</a></li>
                            <li><a href="#sobre">Sobre</a></li>
                            <li><a href="#contato">Contato</a></li>
                        </ul>
                    </nav>
                    <div class="profile-container">
                        <img src="../assets/img/icones/perfilll.png" alt="Perfil" id="profileBtn" width="40px" height="40px" class="profile-img">
                        <?php if ($usuarioLogado): ?>
                            <span class="usuario-nome"><?php echo htmlspecialchars($nomeUsuario); ?></span>
                        <?php endif; ?>
                        <div id="profileDropdown" class="dropdown-menu">
                            <?php if ($usuarioLogado): ?>
                                <div class="dropdown-header">
                                    <p><?php echo htmlspecialchars($nomeUsuario); ?></p>
                                    <?php if ($isAdmin): ?>
                                        <p>
                                            <i class="fas fa-shield-alt"></i> Administrador
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <a href="editar_perfil.php" class="dropdown-item">
                                    <i class="fas fa-user-circle"></i> Meu Perfil
                                </a>
                                <?php if ($isAdmin): ?>
                                    <a href="gerenciar_servicos.php" class="dropdown-item">
                                        <i class="fas fa-wrench"></i> Gerenciar Serviços
                                    </a>
                                    <a href="gerenciar_financeiro.php" class="dropdown-item">
                                        <i class="fas fa-coins"></i> Financeiro
                                    </a>
                                <?php endif; ?>
                                <a href="../backend/sair.php" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt"></i> Sair
                                </a>
                            <?php else: ?>
                                <a href="cadastrar.php" class="dropdown-item">
                                    <i class="fas fa-user-plus"></i> Cadastro
                                </a>
                                <a href="login.php" class="dropdown-item">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <div class="servicos-container">
        <div class="servicos-content">
            <div class="acoes-header">
                <a href="../index.php" class="btn-voltar">
                    <i class="fas fa-arrow-left"></i>
                    Voltar ao Início
                </a>
                <?php if ($isAdmin): ?>
                    <a href="gerenciar_servicos.php" class="btn-gerenciar">
                        <i class="fas fa-wrench"></i>
                        Gerenciar Serviços
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="servicos-header">
                <h1>
                    <i class="fas fa-wrench"></i>
                    Nossos Serviços
                </h1>
                <p>Customização Premium para sua Moto</p>
            </div>
            
            <?php if (count($servicos) > 0): ?>
                <div class="servicos-grid">
                    <?php foreach ($servicos as $servico): ?>
                        <div class="servico-card">
                            <div class="servico-header">
                                <div class="servico-icon">
                                    <i class="fas fa-<?php 
                                        $nome_lower = strtolower($servico['nome']);
                                        if (strpos($nome_lower, 'pintura') !== false) {
                                            echo 'paint-brush';
                                        } elseif (strpos($nome_lower, 'motor') !== false) {
                                            echo 'gears';
                                        } elseif (strpos($nome_lower, 'rodas') !== false || strpos($nome_lower, 'pneu') !== false) {
                                            echo 'cog';
                                        } elseif (strpos($nome_lower, 'som') !== false || strpos($nome_lower, 'áudio') !== false) {
                                            echo 'volume-up';
                                        } elseif (strpos($nome_lower, 'banco') !== false || strpos($nome_lower, 'assento') !== false) {
                                            echo 'chair';
                                        } else {
                                            echo 'tools';
                                        }
                                    ?>"></i>
                                </div>
                                <h3><?= htmlspecialchars($servico['nome']) ?></h3>
                            </div>
                            <div class="servico-body">
                                <p class="servico-descricao">
                                    <?= htmlspecialchars($servico['descricao']) ?>
                                </p>
                                <div class="servico-preco">
                                    R$ <?= number_format($servico['preco'], 2, ',', '.') ?>
                                </div>
                                <button class="btn-solicitar" onclick="alert('Entre em contato conosco para solicitar este serviço!')">
                                    <i class="fas fa-envelope"></i>
                                    Solicitar Orçamento
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="sem-servicos">
                    <i class="fas fa-inbox"></i>
                    <p>Nenhum serviço disponível no momento.</p>
                    <p style="font-size: 0.9em; color: #999; margin-top: 10px;">
                        Por favor, volte mais tarde.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        const profileBtn = document.getElementById('profileBtn');
        const profileDropdown = document.getElementById('profileDropdown');

        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('active');
        });

        document.addEventListener('click', function(e) {
            if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.remove('active');
            }
        });

        const dropdownItems = document.querySelectorAll('.dropdown-item');
        dropdownItems.forEach(item => {
            item.addEventListener('click', function() {
                profileDropdown.classList.remove('active');
            });
        });
    </script>
</body>
</html>
