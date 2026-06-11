<?php
    require_once 'conexao.php';
    session_start();

    if (!isset($_SESSION['id'])) {
        header('Location: login.php');
        exit();
    }

    $usuarioId = $_SESSION['id'];

    $sqlVerificaAdmin = "SELECT is_admin FROM users WHERE id = ?";
    $stmtAdmin = $conn->prepare($sqlVerificaAdmin);
    $stmtAdmin->bind_param("i", $usuarioId);
    $stmtAdmin->execute();
    $resultAdmin = $stmtAdmin->get_result();
    $usuarioAdmin = $resultAdmin->fetch_assoc();
    $stmtAdmin->close();

    if (!$usuarioAdmin || $usuarioAdmin['is_admin'] != 1) {
        header('Location: index.php');
        exit();
    }


    $mensagem = '';
    $tipo_mensagem = '';

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
    $movimentacao_id = $_GET['id'] ?? null;
    $movimentacao = null;

    if (($acao == 'editar' || $acao == 'deletar') && $movimentacao_id) {
        $sqlMovimentacao = "SELECT * FROM financeiro_movimentacoes WHERE id = ?";
        $stmtMovimentacao = $conn->prepare($sqlMovimentacao);
        $stmtMovimentacao->bind_param("i", $movimentacao_id);
        $stmtMovimentacao->execute();
        $resultMovimentacao = $stmtMovimentacao->get_result();
        $movimentacao = $resultMovimentacao->fetch_assoc();
        $stmtMovimentacao->close();

        if (!$movimentacao) {
            $acao = 'listar';
            $mensagem = 'Movimentacao financeira nao encontrada.';
            $tipo_mensagem = 'erro';
        }
    }

    $sqlListar = "SELECT * FROM financeiro_movimentacoes ORDER BY data_movimento DESC, id DESC";
    $resultListar = $conn->query($sqlListar);
    $movimentacoes = [];
    while ($row = $resultListar->fetch_assoc()) {
        $movimentacoes[] = $row;
    }

    $totalEntradas = 0;
    $totalSaidas = 0;
    $totalPendente = 0;

    foreach ($movimentacoes as $item) {
        if ($item['status'] == 'cancelado') {
            continue;
        }

        if ($item['status'] == 'pendente') {
            $totalPendente += (float) $item['valor'];
            continue;
        }

        if ($item['tipo'] == 'entrada') {
            $totalEntradas += (float) $item['valor'];
        } else {
            $totalSaidas += (float) $item['valor'];
        }
    }

    $saldo = $totalEntradas - $totalSaidas;

    function valorFinanceiro($valor) {
        return 'R$ ' . number_format((float) $valor, 2, ',', '.');
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financeiro - Thunder Motors Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="menu.css">
    <link rel="stylesheet" href="menu_admin.css">
    <link rel="stylesheet" href="financeiro.css">
</head>
<body>

    <?php include 'menu_admin.php'; ?>

    <div class="admin-container">
        <div class="admin-header">
            <h1>
                <i class="fas fa-coins"></i>
                Financeiro
            </h1>
            <p>Painel financeiro administrativo - Thunder Motors</p>
        </div>

        <div class="admin-content">
            <a href="index.php" class="btn-voltar">
                <i class="fas fa-arrow-left"></i>
                Voltar ao Inicio
            </a>

            <?php if ($mensagem): ?>
                <div class="alerta alerta-<?= $tipo_mensagem ?>">
                    <i class="fas fa-<?= $tipo_mensagem == 'sucesso' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>

            <div class="resumo-grid">
                <div class="resumo-item entrada">
                    <span>Entradas</span>
                    <strong><?= valorFinanceiro($totalEntradas) ?></strong>
                </div>
                <div class="resumo-item saida">
                    <span>Saidas</span>
                    <strong><?= valorFinanceiro($totalSaidas) ?></strong>
                </div>
                <div class="resumo-item saldo <?= $saldo >= 0 ? 'positivo' : 'negativo' ?>">
                    <span>Saldo</span>
                    <strong><?= valorFinanceiro($saldo) ?></strong>
                </div>
                <div class="resumo-item pendente">
                    <span>Pendente</span>
                    <strong><?= valorFinanceiro($totalPendente) ?></strong>
                </div>
            </div>

            <?php if ($acao == 'listar'): ?>
                <div class="painel">
                    <div class="painel-header">
                        <h2>Movimentacoes Financeiras</h2>
                        <a href="?acao=criar" class="btn-novo">
                            <i class="fas fa-plus"></i>
                            Nova Movimentacao
                        </a>
                    </div>

                    <?php if (count($movimentacoes) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Descricao</th>
                                    <th>Categoria</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Acoes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movimentacoes as $item): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($item['data_movimento'])) ?></td>
                                        <td>
                                            <span class="badge badge-<?= htmlspecialchars($item['tipo']) ?>">
                                                <i class="fas fa-<?= $item['tipo'] == 'entrada' ? 'arrow-up' : 'arrow-down' ?>"></i>
                                                <?= ucfirst($item['tipo']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($item['descricao']) ?></strong><br>
                                            <small><?= htmlspecialchars($item['forma_pagamento']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($item['categoria']) ?></td>
                                        <td><strong><?= valorFinanceiro($item['valor']) ?></strong></td>
                                        <td>
                                            <span class="badge badge-<?= htmlspecialchars($item['status']) ?>">
                                                <?= ucfirst($item['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="acoes">
                                                <a href="?acao=editar&id=<?= $item['id'] ?>" class="btn-editar">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <a href="?acao=deletar&id=<?= $item['id'] ?>" class="btn-deletar" onclick="return confirm('Tem certeza que deseja deletar esta movimentacao?')">
                                                    <i class="fas fa-trash"></i> Deletar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="sem-registros">
                            <i class="fas fa-receipt"></i>
                            <p>Nenhuma movimentacao financeira cadastrada.</p>
                            <a href="?acao=criar" class="btn-novo">
                                <i class="fas fa-plus"></i>
                                Criar Primeiro Registro
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($acao == 'criar' || $acao == 'editar'): ?>
                <div class="form-container">
                    <h2>
                        <i class="fas fa-<?= $acao == 'criar' ? 'plus-circle' : 'edit' ?>"></i>
                        <?= $acao == 'criar' ? 'Nova Movimentacao' : 'Editar Movimentacao' ?>
                    </h2>

                    <form method="POST" action="processar_financeiro.php">
                        <input type="hidden" name="acao" value="<?= $acao ?>">
                        <?php if ($acao == 'editar'): ?>
                            <input type="hidden" name="id" value="<?= $movimentacao['id'] ?>">
                        <?php endif; ?>

                        <div class="form-rows">
                            <div class="form-group">
                                <label for="tipo">Tipo *</label>
                                <select id="tipo" name="tipo" required>
                                    <option value="entrada" <?= ($acao == 'editar' && $movimentacao['tipo'] == 'entrada') ? 'selected' : '' ?>>Entrada</option>
                                    <option value="saida" <?= ($acao == 'editar' && $movimentacao['tipo'] == 'saida') ? 'selected' : '' ?>>Saida</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="data_movimento">Data *</label>
                                <input type="date" id="data_movimento" name="data_movimento" value="<?= $acao == 'editar' ? htmlspecialchars($movimentacao['data_movimento']) : date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <div class="form-rows">
                            <div class="form-group">
                                <label for="descricao">Descricao *</label>
                                <input type="text" id="descricao" name="descricao" maxlength="150" placeholder="Ex: Pintura customizada cliente Joao" value="<?= $acao == 'editar' ? htmlspecialchars($movimentacao['descricao']) : '' ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="valor">Valor (R$) *</label>
                                <input type="number" id="valor" name="valor" step="0.01" min="0.01" placeholder="0.00" value="<?= $acao == 'editar' ? htmlspecialchars($movimentacao['valor']) : '' ?>" required>
                            </div>
                        </div>

                        <div class="form-rows">
                            <div class="form-group">
                                <label for="categoria">Categoria *</label>
                                <input type="text" id="categoria" name="categoria" maxlength="80" placeholder="Ex: Servicos, Pecas, Aluguel" value="<?= $acao == 'editar' ? htmlspecialchars($movimentacao['categoria']) : '' ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="forma_pagamento">Forma de Pagamento *</label>
                                <input type="text" id="forma_pagamento" name="forma_pagamento" maxlength="80" placeholder="Ex: Pix, Cartao, Dinheiro" value="<?= $acao == 'editar' ? htmlspecialchars($movimentacao['forma_pagamento']) : '' ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" required>
                                <option value="pendente" <?= ($acao == 'editar' && $movimentacao['status'] == 'pendente') ? 'selected' : '' ?>>Pendente</option>
                                <option value="pago" <?= ($acao == 'editar' && $movimentacao['status'] == 'pago') ? 'selected' : ($acao == 'criar' ? 'selected' : '') ?>>Pago</option>
                                <option value="cancelado" <?= ($acao == 'editar' && $movimentacao['status'] == 'cancelado') ? 'selected' : '' ?>>Cancelado</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="observacao">Observacao</label>
                            <textarea id="observacao" name="observacao" placeholder="Detalhes adicionais da movimentacao..."><?= $acao == 'editar' ? htmlspecialchars($movimentacao['observacao'] ?? '') : '' ?></textarea>
                        </div>

                        <div class="form-buttons">
                            <button type="submit" class="btn-salvar">
                                <i class="fas fa-save"></i>
                                <?= $acao == 'criar' ? 'Criar Movimentacao' : 'Atualizar Movimentacao' ?>
                            </button>
                            <a href="gerenciar_financeiro.php" class="btn-cancelar">
                                <i class="fas fa-times"></i>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($acao == 'deletar'): ?>
                <div class="form-container">
                    <h2 style="color: var(--danger);">
                        <i class="fas fa-trash"></i>
                        Deletar Movimentacao
                    </h2>

                    <div class="delete-warning">
                        <p><strong>Atencao!</strong> Esta acao e irreversivel.</p>
                        <p>Tem certeza que deseja deletar a movimentacao <strong><?= htmlspecialchars($movimentacao['descricao']) ?></strong>?</p>
                    </div>

                    <form method="POST" action="processar_financeiro.php">
                        <input type="hidden" name="acao" value="deletar">
                        <input type="hidden" name="id" value="<?= $movimentacao['id'] ?>">

                        <div class="form-buttons">
                            <button type="submit" class="btn-deletar">
                                <i class="fas fa-trash"></i>
                                Sim, Deletar
                            </button>
                            <a href="gerenciar_financeiro.php" class="btn-cancelar">
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
