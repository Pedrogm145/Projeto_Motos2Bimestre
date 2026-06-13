<?php
    require_once __DIR__ . '/../config/conexao.php';
    session_start();
    
    // Verificar se usuário está logado
    if (!isset($_SESSION['id'])) {
        header('Location: login.php');
        exit();
    }
    
    $usuarioId = $_SESSION['id'];
    $nomeUsuario = $_SESSION['nome'];
    $emailUsuario = $_SESSION['email'];
    $mensagem = '';
    $tipo_mensagem = '';
    
    // Buscar dados do usuário no banco
    $sql = "SELECT id, nome, email, telefone FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();
    $stmt->close();
    
    // Processar atualização de dados
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $novoNome = trim($_POST['nome'] ?? '');
        $novoEmail = trim($_POST['email'] ?? '');
        $novoTelefone = trim($_POST['telefone'] ?? '');
        $novaSenha = $_POST['senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';
        
        if (empty($novoNome)) {
            $mensagem = 'O nome não pode estar vazio.';
            $tipo_mensagem = 'erro';
        } elseif (!filter_var($novoEmail, FILTER_VALIDATE_EMAIL)) {
            $mensagem = 'Email inválido.';
            $tipo_mensagem = 'erro';
        } elseif (!empty($novaSenha) && strlen($novaSenha) < 6) {
            $mensagem = 'A senha deve ter no mínimo 6 caracteres.';
            $tipo_mensagem = 'erro';
        } elseif (!empty($novaSenha) && $novaSenha !== $confirmarSenha) {
            $mensagem = 'As senhas não coincidem.';
            $tipo_mensagem = 'erro';
        } else {
            // Verificar se o email já existe (de outro usuário)
            $verificaSql = "SELECT id FROM users WHERE email = ? AND id != ?";
            $verificaStmt = $conn->prepare($verificaSql);
            $verificaStmt->bind_param("si", $novoEmail, $usuarioId);
            $verificaStmt->execute();
            $verificaResultado = $verificaStmt->get_result();
            
            if ($verificaResultado->num_rows > 0) {
                $mensagem = 'Este email já está cadastrado por outro usuário.';
                $tipo_mensagem = 'erro';
            } else {
                // Atualizar dados do usuário
                if (!empty($novaSenha)) {
                    $novaSenhaHash = md5($novaSenha);
                    $atualizaSql = "UPDATE users SET nome = ?, email = ?, telefone = ?, senha = ? WHERE id = ?";
                    $atualizaStmt = $conn->prepare($atualizaSql);
                    $atualizaStmt->bind_param("ssssi", $novoNome, $novoEmail, $novoTelefone, $novaSenhaHash, $usuarioId);
                } else {
                    $atualizaSql = "UPDATE users SET nome = ?, email = ?, telefone = ? WHERE id = ?";
                    $atualizaStmt = $conn->prepare($atualizaSql);
                    $atualizaStmt->bind_param("sssi", $novoNome, $novoEmail, $novoTelefone, $usuarioId);
                }
                
                if ($atualizaStmt->execute()) {
                    // Atualizar sessão
                    $_SESSION['nome'] = $novoNome;
                    $_SESSION['email'] = $novoEmail;
                    $nomeUsuario = $novoNome;
                    $emailUsuario = $novoEmail;
                    $usuario['nome'] = $novoNome;
                    $usuario['email'] = $novoEmail;
                    $usuario['telefone'] = $novoTelefone;
                    
                    $mensagem = 'Dados atualizados com sucesso!';
                    $tipo_mensagem = 'sucesso';
                } else {
                    $mensagem = 'Erro ao atualizar dados. Tente novamente.';
                    $tipo_mensagem = 'erro';
                }
                $atualizaStmt->close();
            }
            $verificaStmt->close();
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Thunder Motors</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/editar_perfil.css">
</head>
<body>
    <div class="perfil-container">
        <div class="perfil-card">
            <div class="perfil-header">
                <h1>
                    <i class="fas fa-user-circle"></i>
                    Meu Perfil
                </h1>
            </div>
            
            <div class="perfil-body">
                <?php if (!empty($mensagem)): ?>
                    <div class="mensagem <?php echo $tipo_mensagem; ?>">
                        <i class="fas fa-<?php echo ($tipo_mensagem === 'sucesso') ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo htmlspecialchars($mensagem); ?>
                    </div>
                <?php endif; ?>

                <!-- Visualização de Dados -->
                <div class="info-visualizacao" id="visualizacao">
                    <div class="info-grupo">
                        <label>
                            <i class="fas fa-user"></i> Nome
                        </label>
                        <div class="info-display">
                            <?php echo htmlspecialchars($usuario['nome']); ?>
                        </div>
                    </div>

                    <div class="info-grupo">
                        <label>
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <div class="info-display">
                            <?php echo htmlspecialchars($usuario['email']); ?>
                        </div>
                    </div>

                    <div class="info-grupo">
                        <label>
                            <i class="fas fa-phone"></i> Telefone
                        </label>
                        <div class="info-display">
                            <?php echo !empty($usuario['telefone']) ? htmlspecialchars($usuario['telefone']) : 'Não informado'; ?>
                        </div>
                    </div>

                    <div class="botoes-acao">
                        <button class="btn-editar" onclick="ativarEdicao()">
                            <i class="fas fa-edit"></i> Editar Dados
                        </button>
                    </div>
                    <div class="botoes-acao">
                        <button class="btn-deletar" onclick="confirmarExclusao()">
                            <i class="fas fa-trash-alt"></i> Excluir Perfil
                        </button>
                    </div>
                </div>

                <!-- Formulário de Edição -->
                <form method="POST" class="formulario-editar" id="formularioEdicao">
                    <div class="form-group">
                        <label for="nome">Nome:</label>
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="telefone">Telefone:</label>
                        <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($usuario['telefone'] ?? ''); ?>" placeholder="(11) 99999-9999">
                    </div>

                    <div class="form-group">
                        <label for="senha">Nova Senha (deixe em branco para manter):</label>
                        <input type="password" id="senha" name="senha" placeholder="Mínimo 6 caracteres">
                    </div>

                    <div class="form-group">
                        <label for="confirmar_senha">Confirmar Senha:</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme a nova senha">
                    </div>

                    <div class="botoes-acao">
                        <button type="submit" class="btn-salvar">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                        <button type="button" class="btn-cancelar" onclick="cancelarEdicao()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>

                <a href="../index.php" class="btn-voltar">
                    <i class="fas fa-arrow-left"></i> Voltar para Home
                </a>
            </div>
        </div>
    </div>

    <script>
        function ativarEdicao() {
            document.getElementById('visualizacao').style.display = 'none';
            document.getElementById('formularioEdicao').style.display = 'block';
        }

        function cancelarEdicao() {
            document.getElementById('visualizacao').style.display = 'block';
            document.getElementById('formularioEdicao').style.display = 'none';
        }

        function confirmarExclusao() {
            const confirmacao = confirm('Tem certeza que deseja excluir sua conta? Esta ação é irreversível e todos os seus dados serão perdidos.');
            if (confirmacao) {
                const segundaConfirmacao = confirm('AVISO: Esta ação não pode ser desfeita. Deseja continuar?');
                if (segundaConfirmacao) {
                    window.location.href = '../actions/deletar_perfil.php';
                }
            }
        }
    </script>
</body>
</html>
