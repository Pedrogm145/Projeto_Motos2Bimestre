<?php
    require_once 'conexao.php';
    session_start();
    
    // Verificar se usuário está logado
    if (!isset($_SESSION['id'])) {
        header('Location: login.php');
        exit();
    }
    
    $usuarioId = $_SESSION['id'];
    $nomeUsuario = $_SESSION['nome'];
    
    // Verificar se é admin
    $sqlVerificaAdmin = "SELECT is_admin FROM users WHERE id = ?";
    $stmtAdmin = $conn->prepare($sqlVerificaAdmin);
    $stmtAdmin->bind_param("i", $usuarioId);
    $stmtAdmin->execute();
    $resultAdmin = $stmtAdmin->get_result();
    $usuarioAdmin = $resultAdmin->fetch_assoc();
    $stmtAdmin->close();
    
    // Se não for admin, redireciona
    if (!$usuarioAdmin || $usuarioAdmin['is_admin'] != 1) {
        header('Location: index.php');
        exit();
    }
    
    $mensagem = '';
    $tipo_mensagem = '';
    
    // Verificar mensagens na sessão
    if (isset($_SESSION['sucesso'])) {
        $mensagem = $_SESSION['sucesso'];
        $tipo_mensagem = 'sucesso';
        unset($_SESSION['sucesso']);
    } elseif (isset($_SESSION['erro'])) {
        $mensagem = $_SESSION['erro'];
        $tipo_mensagem = 'erro';
        unset($_SESSION['erro']);
    }
    
    $acao = $_GET['acao'] ?? 'listar';
    $servico_id = $_GET['id'] ?? null;
    
    // Buscar serviço para edição
    $servico = null;
    if (($acao == 'editar' || $acao == 'deletar') && $servico_id) {
        $sqlServico = "SELECT * FROM servicos WHERE id = ?";
        $stmtServico = $conn->prepare($sqlServico);
        $stmtServico->bind_param("i", $servico_id);
        $stmtServico->execute();
        $resultServico = $stmtServico->get_result();
        $servico = $resultServico->fetch_assoc();
        $stmtServico->close();
        
        if (!$servico) {
            $acao = 'listar';
            $mensagem = 'Serviço não encontrado.';
            $tipo_mensagem = 'erro';
        }
    }
    
    // Listar todos os serviços
    $sqlListar = "SELECT * FROM servicos ORDER BY nome ASC";
    $resultListar = $conn->query($sqlListar);
    $servicos = [];
    while ($row = $resultListar->fetch_assoc()) {
        $servicos[] = $row;
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Serviços - Thunder Motors Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="menu.css">
    <link rel="stylesheet" href="menu_admin.css">
    <link rel="stylesheet" href="gerenciar_servico.css">
</head>
<body>

    <!-- Header do Menu -->
    <?php include 'menu_admin.php'; ?>
    
    <div class="admin-container">
        <div class="admin-header">
            <h1>
                <i class="fas fa-wrench"></i>
                Gerenciar Serviços
            </h1>
            <p>Painel de Administração - Thunder Motors</p>
        </div>
        
        <div class="admin-content">
            <a href="index.php" class="btn-voltar">
                <i class="fas fa-arrow-left"></i>
                Voltar ao Início
            </a>
            
            <?php if ($mensagem): ?>
                <div class="alerta alerta-<?= $tipo_mensagem ?>">
                    <i class="fas fa-<?= $tipo_mensagem == 'sucesso' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>
            
            <!-- LISTAR SERVIÇOS -->
            <?php if ($acao == 'listar'): ?>
                <div class="tabela-servicos">
                    <div class="tabela-header">
                        <h2>Lista de Serviços</h2>
                        <a href="?acao=criar" class="btn-novo">
                            <i class="fas fa-plus"></i>
                            Novo Serviço
                        </a>
                    </div>
                    
                    <?php if (count($servicos) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Descrição</th>
                                    <th>Preço</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servicos as $srv): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($srv['nome']) ?></strong></td>
                                        <td><?= htmlspecialchars(substr($srv['descricao'], 0, 50)) ?>...</td>
                                        <td>R$ <?= number_format($srv['preco'], 2, ',', '.') ?></td>
                                        <td>
                                            <?php if ($srv['ativo']): ?>
                                                <span style="color: var(--success); font-weight: 700;">
                                                    <i class="fas fa-check-circle"></i> Ativo
                                                </span>
                                            <?php else: ?>
                                                <span style="color: var(--danger); font-weight: 700;">
                                                    <i class="fas fa-times-circle"></i> Inativo
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="acoes">
                                                <a href="?acao=editar&id=<?= $srv['id'] ?>" class="btn-editar">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <a href="?acao=deletar&id=<?= $srv['id'] ?>" class="btn-deletar" onclick="return confirm('Tem certeza que deseja deletar este serviço?')">
                                                    <i class="fas fa-trash"></i> Deletar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="sem-servicos">
                            <i class="fas fa-inbox"></i>
                            <p>Nenhum serviço cadastrado.</p>
                            <a href="?acao=criar" class="btn-novo" style="margin: 0 auto;">
                                <i class="fas fa-plus"></i>
                                Criar Primeiro Serviço
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- CRIAR/EDITAR SERVIÇO -->
            <?php if ($acao == 'criar' || $acao == 'editar'): ?>
                <div class="form-container">
                    <h2>
                        <i class="fas fa-<?= $acao == 'criar' ? 'plus-circle' : 'edit' ?>"></i>
                        <?= $acao == 'criar' ? 'Criar Novo Serviço' : 'Editar Serviço' ?>
                    </h2>
                    
                    <form method="POST" action="processar_servicos.php">
                        <input type="hidden" name="acao" value="<?= $acao ?>">
                        <?php if ($acao == 'editar'): ?>
                            <input type="hidden" name="id" value="<?= $servico['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="form-rows">
                            <div class="form-group">
                                <label for="nome">Nome do Serviço *</label>
                                <input 
                                    type="text" 
                                    id="nome" 
                                    name="nome" 
                                    placeholder="Ex: Pintura Customizada"
                                    value="<?= $acao == 'editar' ? htmlspecialchars($servico['nome']) : '' ?>"
                                    required
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="preco">Preço (R$) *</label>
                                <input 
                                    type="number" 
                                    id="preco" 
                                    name="preco" 
                                    placeholder="0.00"
                                    step="0.01"
                                    min="0"
                                    value="<?= $acao == 'editar' ? $servico['preco'] : '' ?>"
                                    required
                                >
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="descricao">Descrição do Serviço *</label>
                            <textarea 
                                id="descricao" 
                                name="descricao" 
                                placeholder="Descreva o serviço em detalhes..."
                                required
                            ><?= $acao == 'editar' ? htmlspecialchars($servico['descricao']) : '' ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="ativo">Status</label>
                            <select id="ativo" name="ativo">
                                <option value="1" <?= ($acao == 'editar' && $servico['ativo']) ? 'selected' : ($acao == 'criar' ? 'selected' : '') ?>>
                                    Ativo
                                </option>
                                <option value="0" <?= ($acao == 'editar' && !$servico['ativo']) ? 'selected' : '' ?>>
                                    Inativo
                                </option>
                            </select>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" class="btn-salvar">
                                <i class="fas fa-save"></i>
                                <?= $acao == 'criar' ? 'Criar Serviço' : 'Atualizar Serviço' ?>
                            </button>
                            <a href="gerenciar_servicos.php" class="btn-cancelar">
                                <i class="fas fa-times"></i>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <!-- DELETAR SERVIÇO -->
            <?php if ($acao == 'deletar'): ?>
                <div class="form-container">
                    <h2 style="color: var(--danger);">
                        <i class="fas fa-trash"></i>
                        Deletar Serviço
                    </h2>
                    
                    <div class="delete-warning">
                        <p><strong>Atenção!</strong> Esta ação é irreversível.</p>
                        <p>Tem certeza que deseja deletar permanentemente o serviço <strong><?= htmlspecialchars($servico['nome']) ?></strong>?</p>
                    </div>
                    
                    <form method="POST" action="processar_servicos.php">
                        <input type="hidden" name="acao" value="deletar">
                        <input type="hidden" name="id" value="<?= $servico['id'] ?>">
                        
                        <div class="form-buttons">
                            <button type="submit" class="btn-deletar">
                                <i class="fas fa-trash"></i>
                                Sim, Deletar Serviço
                            </button>
                            <a href="gerenciar_servicos.php" class="btn-cancelar">
                                <i class="fas fa-times"></i>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
