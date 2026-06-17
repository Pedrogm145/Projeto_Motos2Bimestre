<?php
    $configPath = __DIR__ . '/config.local.php';

    if (file_exists($configPath)) {
        $dbConfig = require $configPath;
        $host = $dbConfig['host'];
        $username = $dbConfig['username'];
        $password = $dbConfig['password'];
        $banco = $dbConfig['database'];
    } else {
        $host = 'localhost';
        $username = 'root';
        $password = '';
        $banco = 'usuarios';
    }

    $conn = new mysqli($host, $username, $password, $banco);

    if ($conn->connect_error) {
        die('Erro de conexao: ' . $conn->connect_error);
    }

    $conn->set_charset('utf8mb4');
?>
