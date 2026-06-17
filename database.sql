CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telefone VARCHAR(20) NOT NULL,
    senha VARCHAR(32) NOT NULL,
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL UNIQUE,
    descricao TEXT NOT NULL,
    preco DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS financeiro_movimentacoes (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO servicos (nome, descricao, preco, ativo) VALUES
('Pintura Customizada', 'Pintura personalizada para motos com acabamento premium.', 1200.00, 1),
('Preparacao Visual', 'Ajustes esteticos e personalizacao visual completa.', 850.00, 1),
('Revisao Premium', 'Revisao e acabamento para motos customizadas.', 450.00, 1)
ON DUPLICATE KEY UPDATE
    descricao = VALUES(descricao),
    preco = VALUES(preco),
    ativo = VALUES(ativo);
