<?php
    // Menu simplificado para admin - reutiliza estrutura principal
    $usuarioLogado = isset($_SESSION['nome']);
    $nomeUsuario = $usuarioLogado ? $_SESSION['nome'] : '';
?>

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
                        <li><a href="index.php">Início</a></li>
                        <li><a href="gerenciar_servicos.php">Gerenciar Serviços</a></li>
                        <li><a href="gerenciar_financeiro.php">Financeiro</a></li>
                        <li><a href="perfil.php">Meu Perfil</a></li>
                    </ul>
                </nav>
                <div class="profile-container">
                    <img src="./icones/perfilll.png" alt="Perfil" id="profileBtn" width="40px" height="40px" class="profile-img">
                    <?php if ($usuarioLogado): ?>
                        <span class="usuario-nome"><?php echo htmlspecialchars($nomeUsuario); ?></span>
                    <?php endif; ?>
                    <div id="profileDropdown" class="dropdown-menu">
                        <?php if ($usuarioLogado): ?>
                            <div class="dropdown-header">
                                <p><?php echo htmlspecialchars($nomeUsuario); ?></p>
                                <p style="font-size: 0.9em; color: #ff6b35;">
                                    <i class="fas fa-shield-alt"></i> Administrador
                                </p>
                            </div>
                            <a href="editar_perfil.php" class="dropdown-item">
                                <i class="fas fa-user"></i> Meu Perfil
                            </a>
                            <a href="gerenciar_servicos.php" class="dropdown-item">
                                <i class="fas fa-wrench"></i> Gerenciar Serviços
                            </a>
                            <a href="gerenciar_financeiro.php" class="dropdown-item">
                                <i class="fas fa-coins"></i> Financeiro
                            </a>
                            <hr>
                            <a href="sair.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="dropdown-item">
                                <i class="fas fa-sign-in-alt"></i> Entrar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    // Script do dropdown menu (se não existir já na página)
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');
    
    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', function() {
            profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
        });
        
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.profile-container')) {
                profileDropdown.style.display = 'none';
            }
        });
    }
</script>
