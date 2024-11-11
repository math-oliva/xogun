<?php
// login.php
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'tatuadores_db';
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Coletar dados do formulário
    $email = htmlspecialchars(trim($_POST['email']));
    $senha = $_POST['senha'];

    // Verificar se o e-mail existe no banco de dados
    $sql = "SELECT * FROM tatuadores WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // E-mail encontrado, pegar os dados
        $usuario = $result->fetch_assoc();

        // Verificar se a senha fornecida corresponde ao hash no banco de dados
        if (password_verify($senha, $usuario['senha'])) {
            // Senha correta, iniciar sessão
            $_SESSION['tatuador_id'] = $usuario['id'];
            $_SESSION['nome'] = $usuario['nome_completo'];
            echo "Login bem-sucedido!";
            // Redirecionar para a página principal ou painel do tatuador
            header("Location: admin.php");
            exit();
        } else {
            echo "Erro: Senha incorreta.";
        }
    } else {
        echo "Erro: E-mail não encontrado.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login do Tatuador</title>
</head>
<body>
    <h1>Login</h1>
    <form action="login.php" method="POST">
        <label for="email">E-mail:</label><br>
        <input type="email" id="email" name="email" required><br><br>
        
        <label for="senha">Senha:</label><br>
        <input type="password" id="senha" name="senha" required><br><br>
        
        <input type="submit" value="Entrar">
    </form>
</body>
</html>
