<?php
// cadastro.php
// Conectar ao banco de dados
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'tatuadores_db';
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Coletar dados do formulário e sanitizar as entradas
    $nome_completo = htmlspecialchars(trim($_POST['nome_completo']));
    $nome_artistico = isset($_POST['nome_artistico']) ? htmlspecialchars(trim($_POST['nome_artistico'])) : NULL;
    $email = htmlspecialchars(trim($_POST['email']));
    $telefone = isset($_POST['telefone']) ? htmlspecialchars(trim($_POST['telefone'])) : NULL;
    $senha = $_POST['senha'];  // A senha que o usuário fornece

    // Verificar se o e-mail já está registrado
    $sql_check_email = "SELECT * FROM tatuadores WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check_email);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Se o e-mail já existe, exibe uma mensagem
        echo "Erro: Este e-mail já está registrado.";
        $stmt_check->close();
        $conn->close();
        exit; // Interrompe o código para não continuar a inserção
    }

    // Validar a senha (mínimo de 6 caracteres)
    if (strlen($senha) < 6) {
        echo "Erro: A senha deve ter pelo menos 6 caracteres.";
        exit;
    }

    // Hash da senha (segurança)
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT); // Criação do hash da senha

    // Variável para armazenar a foto de perfil
    $foto_perfil = NULL;

    // Processamento do upload de imagem
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == UPLOAD_ERR_OK) {
        // Verificando se o arquivo é uma imagem válida
        $fileTmpPath = $_FILES['foto_perfil']['tmp_name'];
        $fileName = $_FILES['foto_perfil']['name'];
        $fileSize = $_FILES['foto_perfil']['size'];
        $fileType = $_FILES['foto_perfil']['type'];
        
        // Caminho do diretório para armazenar as imagens
        $uploadDir = 'uploads/';
        $filePath = $uploadDir . basename($fileName);

        // Verificando o tipo de arquivo (somente imagens)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($fileType, $allowedTypes)) {
            echo "Erro: Somente arquivos de imagem (JPEG, PNG, GIF) são permitidos.";
            exit;
        }

        // Limitar o tamanho do arquivo (exemplo: 5MB)
        if ($fileSize > 5 * 1024 * 1024) {
            echo "Erro: O arquivo é muito grande. O tamanho máximo permitido é 5MB.";
            exit;
        }

        // Mover o arquivo para o diretório de uploads
        if (move_uploaded_file($fileTmpPath, $filePath)) {
            $foto_perfil = $filePath; // Salvar o caminho do arquivo
        } else {
            echo "Erro ao fazer o upload da foto.";
            exit;
        }
    }

    // Usar prepared statements para evitar SQL Injection
    $sql = "INSERT INTO tatuadores (nome_completo, nome_artistico, email, telefone, senha, foto_perfil) 
            VALUES (?, ?, ?, ?, ?, ?)";

    // Preparar e vincular os parâmetros
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $nome_completo, $nome_artistico, $email, $telefone, $senha_hash, $foto_perfil);

    if ($stmt->execute()) {
        echo "Cadastro realizado com sucesso!";
    } else {
        echo "Erro: " . $stmt->error;
    }

    $stmt->close();
    $stmt_check->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Tatuador</title>
</head>
<body>
    <h1>Cadastro de Tatuador</h1>
    <form action="cadastro.php" method="POST" enctype="multipart/form-data">
        <label for="nome_completo">Nome Completo:</label><br>
        <input type="text" id="nome_completo" name="nome_completo" required><br><br>

        <label for="nome_artistico">Nome Artístico (opcional):</label><br>
        <input type="text" id="nome_artistico" name="nome_artistico"><br><br>

        <label for="email">E-mail:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="telefone">Telefone:</label><br>
        <input type="text" id="telefone" name="telefone"><br><br>

        <label for="senha">Senha:</label><br>
        <input type="password" id="senha" name="senha" required><br><br>

        <label for="foto_perfil">Foto de Perfil (opcional):</label><br>
        <input type="file" id="foto_perfil" name="foto_perfil"><br><br>

        <input type="submit" value="Cadastrar">
    </form>
</body>
</html>
