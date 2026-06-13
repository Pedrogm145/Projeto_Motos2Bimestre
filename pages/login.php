<?php
    require_once __DIR__ . '/../config/conexao.php';
    
    $erro = '';
    $sucesso = '';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        
        // Validação
        if (empty($email) || empty($senha)) {
            $erro = 'Email e senha são obrigatórios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Insira um email válido.';
        } else {
            // Buscar usuário no banco
            $sql = "SELECT id, nome, email, senha FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows == 0) {
                $erro = 'Email ou senha incorretos.';
            } else {
                $usuario = $resultado->fetch_assoc();
                
                // Comparar senha com md5
                if (md5($senha) !== $usuario['senha']) {
                    $erro = 'Email ou senha incorretos.';
                } else {
                    // Login bem-sucedido
                    session_start();
                    $_SESSION['id'] = $usuario['id'];
                    $_SESSION['nome'] = $usuario['nome'];
                    $_SESSION['email'] = $usuario['email'];
                    
                    $sucesso = 'Login realizado com sucesso! Redirecionando...';
                    header("Refresh: 1; url=../index.php");
                }
            }
            $stmt->close();
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Thunder Motors</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>
                    <i class="fas fa-motorcycle"></i>
                    Thunder Motors
                </h1>
                <p>Faça login na sua conta</p>
            </div>

            <div class="login-content">
                <?php if ($erro): ?>
                    <div class="alerta alerta-erro">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <div class="alerta alerta-sucesso">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($sucesso) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="seu@email.com"
                            required
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="senha">
                            <i class="fas fa-lock"></i> Senha
                        </label>
                        <input 
                            type="password" 
                            id="senha" 
                            name="senha" 
                            placeholder="Sua senha"
                            required
                        >
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Entrar
                    </button>
                </form>
            </div>

            <div class="login-footer">
                <p>Não tem conta? <a href="cadastrar.php">Cadastre-se aqui</a></p>
                <p><a href="../index.php" class="btn-voltar-home"><i class="fas fa-home"></i> Voltar para Página Inicial</a></p>
            </div>
        </div>
    </div>
</body>
</html>

