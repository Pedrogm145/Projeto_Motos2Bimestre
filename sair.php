<?php
    session_start();
    
    // Destruir a sessão do usuário
    session_destroy();
    
    // Limpar cookies de autenticação (se existirem)
    if (isset($_COOKIE['usuario'])) {
        setcookie('usuario', '', time() - 3600, '/');
    }
    
    // Redirecionar para a página inicial
    header('Location: index.php');
    exit();
?>
