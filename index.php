<?php
    require_once __DIR__ . '/conexao/conexao.php';
    session_start();
    
    // Verificar se usuário está logado
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
    
    // Verificar se conta foi deletada
    $contaDeletada = isset($_GET['deletado']) && $_GET['deletado'] === 'true';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thunder Motors - Customização de Motos Premium</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/index.js"></script>
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
               <div id = "teste">
                <nav id="nav-menu">
                    <ul>
                        <li><a href="#servicos">Serviços</a></li>
                        <li><a href="#portfolio">Portfólio</a></li>
                        <li><a href="#sobre">Sobre</a></li>
                        <li><a href="#contato">Contato</a></li>
                    </ul>
                </nav>
                <div class="profile-container">
                    <img src="assets/img/icones/perfilll.png" alt="Perfil" id="profileBtn" width="40px" height="40px" class="profile-img">
                    <?php if ($usuarioLogado): ?>
                        <span class="usuario-nome"><?php echo htmlspecialchars($nomeUsuario); ?></span>
                    <?php endif; ?>
                    <div id="profileDropdown" class="dropdown-menu">
                        <?php if ($usuarioLogado): ?>
                            <div class="dropdown-header">
                                <p><?php echo htmlspecialchars($nomeUsuario); ?></p>
                                <?php if ($isAdmin): ?>
                                    <p style="font-size: 0.9em; color: #ff6b35;">
                                        <i class="fas fa-shield-alt"></i> Administrador
                                    </p>
                                <?php endif; ?>
                            </div>
                            <a href="paginas/editar_perfil.php" class="dropdown-item">
                                <i class="fas fa-user-circle"></i> Meu Perfil
                            </a>
                            <?php if ($isAdmin): ?>
                                <a href="paginas/gerenciar_servicos.php" class="dropdown-item">
                                    <i class="fas fa-wrench"></i> Gerenciar Serviços
                                </a>
                                <a href="paginas/gerenciar_financeiro.php" class="dropdown-item">
                                    <i class="fas fa-coins"></i> Financeiro
                                </a>
                            <?php endif; ?>
                            <a href="backend/sair.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </a>
                        <?php else: ?>
                            <a href="paginas/cadastrar.php" class="dropdown-item">
                                <i class="fas fa-user-plus"></i> Cadastro
                            </a>
                            <a href="paginas/login.php" class="dropdown-item">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </header>

    <?php if ($contaDeletada): ?>
        <div id="alertaDeletado" style="background-color: #e8f5e9; color: #2e7d32; padding: 20px; text-align: center; border-bottom: 3px solid #4caf50; font-weight: 500; transition: opacity 0.5s ease, max-height 0.5s ease; max-height: 100px; overflow: hidden;">
            <i class="fas fa-check-circle"></i> Sua conta foi excluída com sucesso. Esperamos vê-lo em breve!
        </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Thunder Motors</h1>
                <p>Transformamos suas duas rodas em uma obra-prima única e personalizada</p>
                <div class="hero-buttons">
                    <a href="#contato" class="btn btn-primary">Solicitar Orçamento</a>
                    <a href="#portfolio" class="btn btn-secondary">Ver Portfólio</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Serviços -->
    <section class="servicos" id="servicos">
        <div class="container">
            <h2 class="section-title">Nossos Serviços</h2>
            <div class="servicos-grid">
                <div class="servico-card">
                    <i class="fas fa-paint-brush servico-icon"></i>
                    <h3>Pintura Customizada</h3>
                    <p>Designs únicos e personalizados com técnicas modernas de acabamento e proteção UV.</p>
                </div>
                <div class="servico-card">
                    <i class="fas fa-tools servico-icon"></i>
                    <h3>Modificação Mecânica</h3>
                    <p>Upgrade de performance, sistemas de escape e otimização de motor com garantia.</p>
                </div>
                <div class="servico-card">
                    <i class="fas fa-crown servico-icon"></i>
                    <h3>Acessórios Premium</h3>
                    <p>Selim customizado, guidão, espelhos e componentes de alta qualidade.</p>
                </div>
                <div class="servico-card">
                    <i class="fas fa-wrench servico-icon"></i>
                    <h3>Restauração Completa</h3>
                    <p>Reforma completa de motos clássicas com atenção ao detalhe.</p>
                </div>
                <div class="servico-card">
                    <i class="fas fa-lightbulb servico-icon"></i>
                    <h3>Iluminação LED</h3>
                    <p>Sistemas de LED modernos, faróis HID e iluminação ambiente personalizada.</p>
                </div>
                <div class="servico-card">
                    <i class="fas fa-tachometer-alt servico-icon"></i>
                    <h3>Consultoria Técnica</h3>
                    <p>Orientação profissional para manutenção e otimização da sua moto.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Portfólio -->
    <section class="portfolio" id="portfolio">
        <div class="container">
            <h2 class="section-title">Portfólio de Trabalhos</h2>
            <div class="portfolio-grid">
                <div class="portfolio-item portfolio-harley">
                    <div class="portfolio-overlay">
                        <div class="portfolio-info">
                            <h3>Harley Davidson Vermelha</h3>
                            <p>Restauração e customização completa</p>
                        </div>
                    </div>
                </div>
                <div class="portfolio-item portfolio-yamaha">
                    <div class="portfolio-overlay">
                        <div class="portfolio-info">
                            <h3>Yamaha YZF R3</h3>
                            <p>Modificação de performance</p>
                        </div>
                    </div>
                </div>
                <div class="portfolio-item portfolio-street">
                    <div class="portfolio-overlay">
                        <div class="portfolio-info">
                            <h3>Custom Street Fighter</h3>
                            <p>Design futurista e iluminação LED</p>
                        </div>
                    </div>
                </div>
                <div class="portfolio-item portfolio-classica">
                    <div class="portfolio-overlay">
                        <div class="portfolio-info">
                            <h3>Moto Clássica Restaurada</h3>
                            <p>Restauração de época vintage</p>
                        </div>
                    </div>
                </div>
                <div class="portfolio-item portfolio-naked">
                    <div class="portfolio-overlay">
                        <div class="portfolio-info">
                            <h3>Naked Bike Personalizada</h3>
                            <p>Customização aerodinâmica</p>
                        </div>
                    </div>
                </div>
                <div class="portfolio-item portfolio-chopper">
                    <div class="portfolio-overlay">
                        <div class="portfolio-info">
                            <h3>Chopper Art Deco</h3>
                            <p>Estilo único e exclusivo</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sobre -->
    <section class="sobre" id="sobre">
        <div class="container">
            <h2 class="section-title">Sobre a Thunder Motors</h2>
            <div class="sobre-content">
                <div class="sobre-text">
                    <h2>Somos Apaixonados por Motos</h2>
                    <p>Com mais de 15 anos de experiência, a Thunder Motors é referência em customização de motos na região. Nossa equipe é composta por profissionais altamente qualificados que transformam sonhos em realidade sobre duas rodas.</p>
                    <p>Cada projeto é único e realizado com atenção aos mínimos detalhes, utilizando apenas materiais de primeira qualidade e técnicas modernas de customização.</p>
                    
                    <div class="sobre-features">
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <h4>Certificado</h4>
                                <p>Profissionais qualificados</p>
                            </div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-star"></i>
                            <div>
                                <h4>Garantia</h4>
                                <p>12 meses de cobertura</p>
                            </div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <h4>Qualidade</h4>
                                <p>Materiais premium</p>
                            </div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-users"></i>
                            <div>
                                <h4>Suporte</h4>
                                <p>Atendimento exclusivo</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sobre-image">
                    <i class="fas fa-motorcycle"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Contato -->
    <section class="contato" id="contato">
        <div class="container">
            <h2 class="section-title" style="color: white;">Fale Conosco</h2>
            <div class="contato-content">
                <div class="contato-info">
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div class="info-item-content">
                            <h4>Localização</h4>
                            <p>Rua das Motos, 123<br>São Paulo, SP 01234-567</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <div class="info-item-content">
                            <h4>Telefone</h4>
                            <p>(11) 9999-9999<br>(11) 3333-3333</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <div class="info-item-content">
                            <h4>Email</h4>
                            <p>contato@thundermotors.com<br>orcamento@thundermotors.com</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <div class="info-item-content">
                            <h4>Horário de Funcionamento</h4>
                            <p>Seg a Sex: 08:00 - 18:00<br>Sab: 09:00 - 14:00</p>
                        </div>
                    </div>
                </div>
                <form class="contato-form" onsubmit="handleSubmit(event)">
                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="tel" id="telefone" name="telefone" required>
                    </div>
                    <div class="form-group">
                        <label for="mensagem">Mensagem</label>
                        <textarea id="mensagem" name="mensagem" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Enviar Mensagem</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="social-links">
                <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
            <p>&copy; 2024 Thunder Motors - Customização de Motos. Todos os direitos reservados.</p>
            <p style="font-size: 0.9rem; margin-top: 0.5rem;">Desenvolvido com <i class="fas fa-heart" style="color: var(--primary);"></i> para amantes de motos</p>
        </div>
    </footer>
</body>
</html>
