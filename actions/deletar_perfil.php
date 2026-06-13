<?php
    require_once __DIR__ . '/../config/conexao.php';
    session_start();
    
    // Verificar se usuário está logado
    if (!isset($_SESSION['id'])) {
        header('Location: ../pages/login.php');
        exit();
    }
    
    $usuarioId = $_SESSION['id'];
    
    // Deletar usuário do banco de dados
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuarioId);
    
    if ($stmt->execute()) {
        // Destruir a sessão
        session_destroy();
        
        // Limpar cookies
        if (isset($_COOKIE['usuario'])) {
            setcookie('usuario', '', time() - 3600, '/');
        }
        
        // Redirecionar para página inicial com mensagem
        header('Location: ../index.php?deletado=true');
        exit();
    } else {
        // Se houver erro, voltar para o perfil
        header('Location: ../pages/editar_perfil.php?erro=true');
        exit();
    }
    
    $stmt->close();
    $conn->close();
?>
