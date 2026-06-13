<?php
   $host = 'localhost';
   $username = 'root';
   $password = '';
   $banco = "usuarios";

    $conn = new mysqli($host, $username, $password, $banco);
   if ($conn->connect_error) {die('Erro de conexão: ' . $conn->connect_error);}
    $conn->set_charset('utf8');
?>