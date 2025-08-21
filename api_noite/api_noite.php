<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, PUT");
header("Access-Control-Allow-Headers: Content-Type");

// Conexão com o banco de dados
include 'db.php';

$method = $_SERVER["REQUEST_METHOD"];

if($method == "POST"){
    $data = json_decode(file_get_contents("php://input"), true);

    if(isset(
        $data["nome"],
        $data["email"],
        $data["senha"],
        $data["telefone"],
        $data["endereco"],
        $data["estado"],
        $data["data_nascimento"]
    )){
        $nome = $data["nome"];
        $email = $data["email"];
        $senha = $data["senha"];
        $telefone = $data["telefone"];
        $endereco = $data["endereco"];
        $estado = $data["estado"];
        $data_nascimento = $data["data_nascimento"];

        // Criptografar a senha
        $senha_hash = password_hash($senha, PASSWORD_BCRYPT);

        // Verificar se o email já está cadastrado
        $verificaEmail = $conn->prepare("SELECT id FROM api_usuarios WHERE email = ?");
        $verificaEmail->bind_param("s", $email);
        $verificaEmail->execute();
        $verificaEmail->store_result();

        if ($verificaEmail->num_rows > 0) {
            http_response_code(400);
            echo json_encode(["erro" => "Este email já está cadastrado."], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Limpar espaços, traços, barras, parênteses, etc.
        $telefone_limpo = preg_replace('/[\s\-\(\)\/]/', '', $telefone); // Remove esses caracteres

        //  Verificar se sobrou algum caractere que não seja número
        if (!ctype_digit($telefone_limpo)) {
            http_response_code(400);
            echo json_encode(["erro" => "Telefone inválido. Use apenas números entre 9 e 12 dígitos. Letras e símbolos não são permitidos."], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Verificar o tamanho final do número
        if (strlen($telefone_limpo) < 9 || strlen($telefone_limpo) > 12) {
            http_response_code(400);
            echo json_encode(["erro" => "Telefone deve ter entre 9 e 12 dígitos numéricos."], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Substitui o telefone original pelo formatado/limpo
        $telefone = $telefone_limpo;

        // Inserir no banco
        $sql = "INSERT INTO api_usuarios(nome, email, senha, telefone, endereco, estado, data_nascimento) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $nome, $email, $senha_hash, $telefone, $endereco, $estado, $data_nascimento);

        if($stmt->execute()){
            $id = $stmt->insert_id;
            $result = $conn->query("SELECT * FROM api_usuarios WHERE id=$id");
            $cliente = $result->fetch_assoc();

            echo json_encode([
                "mensagem" => "Cliente cadastrado com sucesso", 
                "cliente" => $cliente
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao cadastrar cliente."], JSON_UNESCAPED_UNICODE);
        }

    } else {
        http_response_code(400);
        echo json_encode(["erro"=> "Todos os campos são obrigatórios"], JSON_UNESCAPED_UNICODE);
        exit();
    }
}
?>
