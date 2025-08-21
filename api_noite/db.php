<?php

//conexão com banco
$host = "localhost";
$user = "root";
$pass = "aluno"; 
$db = "sistema_api";
$conn = new mysqli($host, $user, $pass, $db);
if($conn->connect_error){
    http_response_code(500);
    echo json_encode(["erro" => "Falha na conexão"], 
    JSON_UNESCAPED_UNICODE);
    exit();
}
?>