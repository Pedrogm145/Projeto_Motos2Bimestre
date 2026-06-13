<?php
    require_once __DIR__ . '/../config/conexao.php';
    session_start();

    if (!isset($_SESSION['id'])) {
        header('Location: ../pages/login.php');
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
        header('Location: ../index.php');
        exit();
    }

    $sqlCriarTabela = "CREATE TABLE IF NOT EXISTS financeiro_movimentacoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo ENUM('entrada', 'saida') NOT NULL,
        descricao VARCHAR(150) NOT NULL,
        categoria VARCHAR(80) NOT NULL,
        valor DECIMAL(10,2) NOT NULL,
        data_movimento DATE NOT NULL,
        forma_pagamento VARCHAR(80) NOT NULL,
        status ENUM('pendente', 'pago', 'cancelado') NOT NULL DEFAULT 'pendente',
        observacao TEXT NULL,
        criado_por INT NULL,
        data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sqlCriarTabela);

    $acao = $_POST['acao'] ?? '';
    $redirect_url = '../pages/gerenciar_financeiro.php';

    function validarData($data) {
        $partes = explode('-', $data);
        return count($partes) == 3 && checkdate((int) $partes[1], (int) $partes[2], (int) $partes[0]);
    }

    function validarCamposFinanceiros($dados) {
        $tiposPermitidos = ['entrada', 'saida'];
        $statusPermitidos = ['pendente', 'pago', 'cancelado'];

        if (!in_array($dados['tipo'], $tiposPermitidos)) {
            return 'Tipo de movimentacao invalido.';
        }

        if (empty($dados['descricao'])) {
            return 'Descricao e obrigatoria.';
        }

        if (empty($dados['categoria'])) {
            return 'Categoria e obrigatoria.';
        }

        if (empty($dados['forma_pagamento'])) {
            return 'Forma de pagamento e obrigatoria.';
        }

        if ($dados['valor'] === '' || !is_numeric($dados['valor']) || $dados['valor'] <= 0) {
            return 'Valor invalido.';
        }

        if (empty($dados['data_movimento']) || !validarData($dados['data_movimento'])) {
            return 'Data invalida.';
        }

        if (!in_array($dados['status'], $statusPermitidos)) {
            return 'Status invalido.';
        }

        return '';
    }

    if ($acao == 'criar' || $acao == 'editar') {
        $dados = [
            'tipo' => $_POST['tipo'] ?? '',
            'descricao' => trim($_POST['descricao'] ?? ''),
            'categoria' => trim($_POST['categoria'] ?? ''),
            'valor' => $_POST['valor'] ?? '',
            'data_movimento' => $_POST['data_movimento'] ?? '',
            'forma_pagamento' => trim($_POST['forma_pagamento'] ?? ''),
            'status' => $_POST['status'] ?? '',
            'observacao' => trim($_POST['observacao'] ?? '')
        ];

        $erroValidacao = validarCamposFinanceiros($dados);
        if ($erroValidacao) {
            $_SESSION['erro'] = $erroValidacao;
            $url = $acao == 'editar' && isset($_POST['id']) ? "$redirect_url?acao=editar&id=" . urlencode($_POST['id']) : "$redirect_url?acao=criar";
            header("Location: $url");
            exit();
        }
    }

    if ($acao == 'criar') {
        $sqlInsert = "INSERT INTO financeiro_movimentacoes (tipo, descricao, categoria, valor, data_movimento, forma_pagamento, status, observacao, criado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param(
            "sssdssssi",
            $dados['tipo'],
            $dados['descricao'],
            $dados['categoria'],
            $dados['valor'],
            $dados['data_movimento'],
            $dados['forma_pagamento'],
            $dados['status'],
            $dados['observacao'],
            $usuarioId
        );

        if ($stmtInsert->execute()) {
            $_SESSION['sucesso'] = 'Movimentacao financeira criada com sucesso!';
            $stmtInsert->close();
            header("Location: $redirect_url");
            exit();
        }

        $_SESSION['erro'] = 'Erro ao criar movimentacao: ' . $conn->error;
        $stmtInsert->close();
        header("Location: $redirect_url?acao=criar");
        exit();
    }

    if ($acao == 'editar') {
        $id = $_POST['id'] ?? '';

        if (empty($id) || !is_numeric($id)) {
            $_SESSION['erro'] = 'ID da movimentacao invalido.';
            header("Location: $redirect_url");
            exit();
        }

        $sqlVerificaExiste = "SELECT id FROM financeiro_movimentacoes WHERE id = ?";
        $stmtVerificaExiste = $conn->prepare($sqlVerificaExiste);
        $stmtVerificaExiste->bind_param("i", $id);
        $stmtVerificaExiste->execute();
        $resultVerificaExiste = $stmtVerificaExiste->get_result();

        if ($resultVerificaExiste->num_rows == 0) {
            $_SESSION['erro'] = 'Movimentacao financeira nao encontrada.';
            $stmtVerificaExiste->close();
            header("Location: $redirect_url");
            exit();
        }
        $stmtVerificaExiste->close();

        $sqlUpdate = "UPDATE financeiro_movimentacoes SET tipo = ?, descricao = ?, categoria = ?, valor = ?, data_movimento = ?, forma_pagamento = ?, status = ?, observacao = ? WHERE id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param(
            "sssdssssi",
            $dados['tipo'],
            $dados['descricao'],
            $dados['categoria'],
            $dados['valor'],
            $dados['data_movimento'],
            $dados['forma_pagamento'],
            $dados['status'],
            $dados['observacao'],
            $id
        );

        if ($stmtUpdate->execute()) {
            $_SESSION['sucesso'] = 'Movimentacao financeira atualizada com sucesso!';
            $stmtUpdate->close();
            header("Location: $redirect_url");
            exit();
        }

        $_SESSION['erro'] = 'Erro ao atualizar movimentacao: ' . $conn->error;
        $stmtUpdate->close();
        header("Location: $redirect_url?acao=editar&id=$id");
        exit();
    }

    if ($acao == 'deletar') {
        $id = $_POST['id'] ?? '';

        if (empty($id) || !is_numeric($id)) {
            $_SESSION['erro'] = 'ID da movimentacao invalido.';
            header("Location: $redirect_url");
            exit();
        }

        $sqlVerificaExiste = "SELECT id, descricao FROM financeiro_movimentacoes WHERE id = ?";
        $stmtVerificaExiste = $conn->prepare($sqlVerificaExiste);
        $stmtVerificaExiste->bind_param("i", $id);
        $stmtVerificaExiste->execute();
        $resultVerificaExiste = $stmtVerificaExiste->get_result();

        if ($resultVerificaExiste->num_rows == 0) {
            $_SESSION['erro'] = 'Movimentacao financeira nao encontrada.';
            $stmtVerificaExiste->close();
            header("Location: $redirect_url");
            exit();
        }

        $movimentacao = $resultVerificaExiste->fetch_assoc();
        $descricao = $movimentacao['descricao'];
        $stmtVerificaExiste->close();

        $sqlDelete = "DELETE FROM financeiro_movimentacoes WHERE id = ?";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bind_param("i", $id);

        if ($stmtDelete->execute()) {
            $_SESSION['sucesso'] = "Movimentacao '{$descricao}' deletada com sucesso!";
            $stmtDelete->close();
            header("Location: $redirect_url");
            exit();
        }

        $_SESSION['erro'] = 'Erro ao deletar movimentacao: ' . $conn->error;
        $stmtDelete->close();
        header("Location: $redirect_url");
        exit();
    }

    $_SESSION['erro'] = 'Acao invalida.';
    header("Location: $redirect_url");
    exit();
?>
