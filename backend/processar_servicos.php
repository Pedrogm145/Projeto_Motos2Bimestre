<?php
    require_once __DIR__ . '/../conexao/conexao.php';
    session_start();
    
    // Verificar se usuário está logado
    if (!isset($_SESSION['id'])) {
        header('Location: ../paginas/login.php');
        exit();
    }
    
    $usuarioId = $_SESSION['id'];
    
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
        header('Location: ../index.php');
        exit();
    }
    
    $acao = $_POST['acao'] ?? '';
    $redirect_url = '../paginas/gerenciar_servicos.php';
    
    // CRIAR SERVIÇO
    if ($acao == 'criar') {
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $preco = $_POST['preco'] ?? '';
        $ativo = $_POST['ativo'] ?? 1;
        
        // Validações
        if (empty($nome)) {
            $_SESSION['erro'] = 'Nome do serviço é obrigatório.';
            header("Location: $redirect_url?acao=criar");
            exit();
        }
        
        if (empty($descricao)) {
            $_SESSION['erro'] = 'Descrição do serviço é obrigatória.';
            header("Location: $redirect_url?acao=criar");
            exit();
        }
        
        if (empty($preco) || !is_numeric($preco) || $preco < 0) {
            $_SESSION['erro'] = 'Preço inválido.';
            header("Location: $redirect_url?acao=criar");
            exit();
        }
        
        // Verificar se nome já existe
        $sqlVerifica = "SELECT id FROM servicos WHERE nome = ?";
        $stmtVerifica = $conn->prepare($sqlVerifica);
        $stmtVerifica->bind_param("s", $nome);
        $stmtVerifica->execute();
        $resultVerifica = $stmtVerifica->get_result();
        
        if ($resultVerifica->num_rows > 0) {
            $_SESSION['erro'] = 'Já existe um serviço com este nome.';
            $stmtVerifica->close();
            header("Location: $redirect_url?acao=criar");
            exit();
        }
        $stmtVerifica->close();
        
        // Inserir serviço
        $sqlInsert = "INSERT INTO servicos (nome, descricao, preco, ativo, data_criacao) VALUES (?, ?, ?, ?, NOW())";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("ssdi", $nome, $descricao, $preco, $ativo);
        
        if ($stmtInsert->execute()) {
            $_SESSION['sucesso'] = 'Serviço criado com sucesso!';
            $stmtInsert->close();
            header("Location: $redirect_url");
            exit();
        } else {
            $_SESSION['erro'] = 'Erro ao criar serviço: ' . $conn->error;
            $stmtInsert->close();
            header("Location: $redirect_url?acao=criar");
            exit();
        }
    }
    
    // EDITAR SERVIÇO
    elseif ($acao == 'editar') {
        $id = $_POST['id'] ?? '';
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $preco = $_POST['preco'] ?? '';
        $ativo = $_POST['ativo'] ?? 1;
        
        // Validações
        if (empty($id) || !is_numeric($id)) {
            $_SESSION['erro'] = 'ID do serviço inválido.';
            header("Location: $redirect_url");
            exit();
        }
        
        if (empty($nome)) {
            $_SESSION['erro'] = 'Nome do serviço é obrigatório.';
            header("Location: $redirect_url?acao=editar&id=$id");
            exit();
        }
        
        if (empty($descricao)) {
            $_SESSION['erro'] = 'Descrição do serviço é obrigatória.';
            header("Location: $redirect_url?acao=editar&id=$id");
            exit();
        }
        
        if (empty($preco) || !is_numeric($preco) || $preco < 0) {
            $_SESSION['erro'] = 'Preço inválido.';
            header("Location: $redirect_url?acao=editar&id=$id");
            exit();
        }
        
        // Verificar se serviço existe
        $sqlVerificaExiste = "SELECT id FROM servicos WHERE id = ?";
        $stmtVerificaExiste = $conn->prepare($sqlVerificaExiste);
        $stmtVerificaExiste->bind_param("i", $id);
        $stmtVerificaExiste->execute();
        $resultVerificaExiste = $stmtVerificaExiste->get_result();
        
        if ($resultVerificaExiste->num_rows == 0) {
            $_SESSION['erro'] = 'Serviço não encontrado.';
            $stmtVerificaExiste->close();
            header("Location: $redirect_url");
            exit();
        }
        $stmtVerificaExiste->close();
        
        // Verificar se nome já existe (em outro serviço)
        $sqlVerificaNome = "SELECT id FROM servicos WHERE nome = ? AND id != ?";
        $stmtVerificaNome = $conn->prepare($sqlVerificaNome);
        $stmtVerificaNome->bind_param("si", $nome, $id);
        $stmtVerificaNome->execute();
        $resultVerificaNome = $stmtVerificaNome->get_result();
        
        if ($resultVerificaNome->num_rows > 0) {
            $_SESSION['erro'] = 'Já existe outro serviço com este nome.';
            $stmtVerificaNome->close();
            header("Location: $redirect_url?acao=editar&id=$id");
            exit();
        }
        $stmtVerificaNome->close();
        
        // Atualizar serviço
        $sqlUpdate = "UPDATE servicos SET nome = ?, descricao = ?, preco = ?, ativo = ? WHERE id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ssdii", $nome, $descricao, $preco, $ativo, $id);
        
        if ($stmtUpdate->execute()) {
            $_SESSION['sucesso'] = 'Serviço atualizado com sucesso!';
            $stmtUpdate->close();
            header("Location: $redirect_url");
            exit();
        } else {
            $_SESSION['erro'] = 'Erro ao atualizar serviço: ' . $conn->error;
            $stmtUpdate->close();
            header("Location: $redirect_url?acao=editar&id=$id");
            exit();
        }
    }
    
    // DELETAR SERVIÇO
    elseif ($acao == 'deletar') {
        $id = $_POST['id'] ?? '';
        
        // Validações
        if (empty($id) || !is_numeric($id)) {
            $_SESSION['erro'] = 'ID do serviço inválido.';
            header("Location: $redirect_url");
            exit();
        }
        
        // Verificar se serviço existe
        $sqlVerificaExiste = "SELECT id, nome FROM servicos WHERE id = ?";
        $stmtVerificaExiste = $conn->prepare($sqlVerificaExiste);
        $stmtVerificaExiste->bind_param("i", $id);
        $stmtVerificaExiste->execute();
        $resultVerificaExiste = $stmtVerificaExiste->get_result();
        
        if ($resultVerificaExiste->num_rows == 0) {
            $_SESSION['erro'] = 'Serviço não encontrado.';
            $stmtVerificaExiste->close();
            header("Location: $redirect_url");
            exit();
        }
        
        $servicoExiste = $resultVerificaExiste->fetch_assoc();
        $nomServico = $servicoExiste['nome'];
        $stmtVerificaExiste->close();
        
        // Deletar serviço
        $sqlDelete = "DELETE FROM servicos WHERE id = ?";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bind_param("i", $id);
        
        if ($stmtDelete->execute()) {
            $_SESSION['sucesso'] = "Serviço '{$nomServico}' deletado com sucesso!";
            $stmtDelete->close();
            header("Location: $redirect_url");
            exit();
        } else {
            $_SESSION['erro'] = 'Erro ao deletar serviço: ' . $conn->error;
            $stmtDelete->close();
            header("Location: $redirect_url");
            exit();
        }
    }
    
    // Ação inválida
    else {
        $_SESSION['erro'] = 'Ação inválida.';
        header("Location: $redirect_url");
        exit();
    }
?>
