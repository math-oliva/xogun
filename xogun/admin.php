<?php
// admin.php
// Conectar ao banco de dados
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'tatuadores_db';
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Autenticação básica
session_start();
if (!isset($_SESSION['tatuador_id'])) {
    header("Location: login.php"); // Redireciona para a página de login se não estiver logado
    exit();
}

// Carregar dados do tatuador
$tatuador_id = $_SESSION['tatuador_id'];
$sql = "SELECT * FROM tatuadores WHERE id = $tatuador_id";
$result = $conn->query($sql);
$tatuador = $result->fetch_assoc();

// Atualizar informações do tatuador
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_tatuador'])) {
        // Atualizar informações do tatuador
        $nome_completo = $_POST['nome_completo'];
        $nome_artistico = $_POST['nome_artistico'] ?? NULL;
        $email = $_POST['email'];
        $telefone = $_POST['telefone'] ?? NULL;
        $foto_perfil = $_POST['foto_perfil'] ?? NULL; // Caso tenha implementado o upload de imagem

        $sql_update = "UPDATE tatuadores SET 
            nome_completo='$nome_completo',
            nome_artistico='$nome_artistico',
            email='$email',
            telefone='$telefone',
            foto_perfil='$foto_perfil'
            WHERE id=$tatuador_id";

        if ($conn->query($sql_update) === TRUE) {
            echo "Informações atualizadas com sucesso!";
        } else {
            echo "Erro ao atualizar: " . $conn->error;
        }
    }

    if (isset($_POST['add_portfolio'])) {
        // Adicionar uma nova imagem ao portfólio
        $descricao = $_POST['descricao'] ?? NULL;
        $estilo = $_POST['estilo'] ?? NULL;

        // Verificar se a foto foi enviada
        if (isset($_FILES['imagem_url']) && $_FILES['imagem_url']['error'] == UPLOAD_ERR_OK) {
            // Processamento do upload da imagem
            $uploadDir = 'uploads/portfolio/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);  // Cria o diretório se não existir
            }

            $fileTmpPath = $_FILES['imagem_url']['tmp_name'];
            $fileName = $_FILES['imagem_url']['name'];
            $filePath = $uploadDir . basename($fileName);

            if (move_uploaded_file($fileTmpPath, $filePath)) {
                // Inserir a imagem no banco de dados
                $sql_insert = "INSERT INTO portfolio (tatuador_id, imagem_url, descricao, estilo)
                               VALUES ($tatuador_id, '$filePath', '$descricao', '$estilo')";
                if ($conn->query($sql_insert) === TRUE) {
                    echo "Imagem adicionada ao portfólio com sucesso!";
                } else {
                    echo "Erro ao adicionar imagem ao portfólio: " . $conn->error;
                }
            } else {
                echo "Erro ao fazer o upload da foto.";
            }
        }
    }

    if (isset($_POST['delete_portfolio'])) {
        // Deletar uma imagem do portfólio
        $portfolio_id = $_POST['portfolio_id'];

        // Verificar se o arquivo existe
        $sql_get_file = "SELECT imagem_url FROM portfolio WHERE id = $portfolio_id";
        $result_file = $conn->query($sql_get_file);
        if ($result_file->num_rows > 0) {
            $file_data = $result_file->fetch_assoc();
            $file_path = $file_data['imagem_url'];

            // Deletar o arquivo do diretório
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Deletar o registro do banco de dados
            $sql_delete = "DELETE FROM portfolio WHERE id = $portfolio_id";
            if ($conn->query($sql_delete) === TRUE) {
                echo "Imagem removida do portfólio com sucesso!";
            } else {
                echo "Erro ao remover imagem do portfólio: " . $conn->error;
            }
        }
    }
}

// Carregar imagens do portfólio
$sql_portfolio = "SELECT * FROM portfolio WHERE tatuador_id = $tatuador_id";
$result_portfolio = $conn->query($sql_portfolio);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Administração do Tatuador</title>
</head>
<body>
    <h1>Administração do Tatuador</h1>
    
    <h2>Dados do Tatuador</h2>
    <form action="admin.php" method="POST" enctype="multipart/form-data">
        <label for="nome_completo">Nome Completo:</label><br>
        <input type="text" id="nome_completo" name="nome_completo" value="<?php echo $tatuador['nome_completo']; ?>" required><br><br>

        <label for="nome_artistico">Nome Artístico (opcional):</label><br>
        <input type="text" id="nome_artistico" name="nome_artistico" value="<?php echo $tatuador['nome_artistico']; ?>"><br><br>

        <label for="email">E-mail:</label><br>
        <input type="email" id="email" name="email" value="<?php echo $tatuador['email']; ?>" required><br><br>

        <label for="telefone">Telefone:</label><br>
        <input type="text" id="telefone" name="telefone" value="<?php echo $tatuador['telefone']; ?>"><br><br>

        <label for="foto_perfil">Foto de Perfil (opcional):</label><br>
        <input type="file" id="foto_perfil" name="foto_perfil"><br><br>

        <input type="submit" name="update_tatuador" value="Atualizar Cadastro">
    </form>

    <hr>
    
    <h2>Gerenciar Portfólio</h2>

    <h3>Adicionar nova imagem ao portfólio</h3>
    <form action="admin.php" method="POST" enctype="multipart/form-data">
        <label for="imagem_url">Imagem:</label><br>
        <input type="file" id="imagem_url" name="imagem_url" required><br><br>

        <label for="descricao">Descrição (opcional):</label><br>
        <textarea id="descricao" name="descricao"></textarea><br><br>

        <label for="estilo">Estilo (opcional):</label><br>
        <input type="text" id="estilo" name="estilo"><br><br>

        <input type="submit" name="add_portfolio" value="Adicionar Imagem ao Portfólio">
    </form>

    <hr>

    <h3>Portfólio Atual</h3>
    <?php
    if ($result_portfolio->num_rows > 0) {
        while ($portfolio = $result_portfolio->fetch_assoc()) {
            echo '<div>';
            echo '<img src="' . $portfolio['imagem_url'] . '" alt="Imagem do portfólio" width="150"><br>';
            echo '<p><strong>Descrição:</strong> ' . $portfolio['descricao'] . '</p>';
            echo '<p><strong>Estilo:</strong> ' . $portfolio['estilo'] . '</p>';
            echo '<form action="admin.php" method="POST">
                    <input type="hidden" name="portfolio_id" value="' . $portfolio['id'] . '">
                    <input type="submit" name="delete_portfolio" value="Remover Imagem">
                  </form>';
            echo '</div><hr>';
        }
    } else {
        echo "<p>Seu portfólio está vazio.</p>";
    }
    ?>

</body>
</html>

<?php
$conn->close();
?>
