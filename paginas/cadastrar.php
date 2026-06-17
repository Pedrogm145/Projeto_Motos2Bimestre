<?php
    require_once __DIR__ . '/../conexao/conexao.php';
    
    $erro = '';
    $sucesso = '';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $telefoneDigitos = preg_replace('/\D/', '', $telefone);
        $senha = $_POST['senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';
        
        // Validação
        if (empty($nome) || empty($email) || empty($telefone) || empty($senha) || empty($confirmar_senha)) {
            $erro = 'Todos os campos são obrigatórios.';
        } elseif (strlen($telefoneDigitos) !== 11) {
            $erro = 'Informe um telefone válido com 11 números.';
        } elseif ($senha !== $confirmar_senha) {
            $erro = 'As senhas não coincidem.';
        } elseif (strlen($senha) < 6) {
            $erro = 'A senha deve ter no mínimo 6 caracteres.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Insira um email válido.';
        } else {
            // Verificar se email já existe
            $sql = "SELECT id FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                $erro = 'Este email já está registrado.';
            } else {
                // Inserir novo usuário
                $sql = "INSERT INTO users (nome, email, telefone, senha) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $senha_hash = md5($senha);
                $stmt->bind_param("ssss", $nome, $email, $telefone, $senha_hash);
                
                
                if ($stmt->execute()) {
                    $sucesso = 'Cadastro realizado com sucesso! Redirecionando para login...';
                    header("Refresh: 2; url=login.php");
                } else {
                    $erro = 'Erro ao registrar. Tente novamente.';
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
    <title>Cadastro - Thunder Motors</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/cadastrar.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/cadastrar.js"></script>
</head>
<body>
    <div class="cadastro-container">
        <div class="cadastro-card">
            <div class="cadastro-header">
                <h1>
                    <i class="fas fa-motorcycle"></i>
                    Thunder Motors
                </h1>
                <p>Crie sua conta agora</p>
            </div>

            <div class="cadastro-body">
                <?php if (!empty($erro)): ?>
                    <div class="alerta alerta-erro">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($erro); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($sucesso)): ?>
                    <div class="alerta alerta-sucesso">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($sucesso); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" onsubmit="return validarFormulario()">
                    <div class="form-group">
                        <label for="nome">
                            <i class="fas fa-user" style="color: #d32f2f;"></i> Nome Completo
                        </label>
                        <input type="text" id="nome" name="nome" placeholder="Seu nome completo" required>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope" style="color: #d32f2f;"></i> Email
                        </label>
                        <input type="email" id="email" name="email" placeholder="seu@email.com" required>
                    </div>

                    <div class="form-group">
                        <label for="telefone">
                            <i class="fas fa-phone" style="color: #d32f2f;"></i> Telefone
                        </label>
                        <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" maxlength="15" inputmode="numeric" required>
                    </div>

                    <div class="form-group-row">
                        <div class="form-group">
                            <label for="senha">
                                <i class="fas fa-lock" style="color: #d32f2f;"></i> Senha
                            </label>
                            <div class="senha-container">
                                <input type="password" id="senha" name="senha" placeholder="Mínimo 6 caracteres" required>
                                <button type="button" class="senha-toggle" onclick="toggleSenha('senha')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirmar_senha">
                                <i class="fas fa-lock" style="color: #d32f2f;"></i> Confirmar Senha
                            </label>
                            <div class="senha-container">
                                <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Repita a senha" required>
                                <button type="button" class="senha-toggle" onclick="toggleSenha('confirmar_senha')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="termos">
                        <i class="fas fa-info-circle"></i>
                        Ao se cadastrar, você concorda com nossos termos de serviço e política de privacidade.
                    </div>

                    <button type="submit" class="btn-cadastro">
                        <i class="fas fa-user-plus"></i> Criar Conta
                    </button>
                </form>
            </div>

            <div class="cadastro-footer">
                <p>Já tem uma conta? <a href="login.php">Faça login aqui</a></p>
                <p><a href="../index.php" class="btn-voltar-home"><i class="fas fa-home"></i> Voltar para Página Inicial</a></p>
            </div>
        </div>
    </div>

</body>
</html>

