<?php
// index.php
// Conectar ao banco de dados
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'tatuadores_db';
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Buscar tatuadores
$sql = "SELECT * FROM tatuadores";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Galeria de Tatuadores</title>
    <style>
        .tatuador-box {
            border: 1px solid #ccc;
            padding: 20px;
            margin: 10px;
            width: 300px;
            float: left;
            text-align: center;
        }
        .tatuador-box img {
            width: 100%;
            height: auto;
        }
        .carousel-inner img {
            width: 100%;
            height: auto;
        }
    </style>
    
    <!-- Inclusão do Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <h1><a href="cadastro.php">Cadastro</a></h1>
    <h1><a href="admin.php">Admin</a></h1>
    <h1>Galeria de Tatuadores</h1>

    <div>
        <?php while ($tatuador = $result->fetch_assoc()) { ?>
            <div class="tatuador-box">
                <h2><?php echo $tatuador['nome_completo']; ?></h2>
                <p><strong>Nome Artístico:</strong> <?php echo $tatuador['nome_artistico']; ?></p>
                <p><strong>E-mail:</strong> <?php echo $tatuador['email']; ?></p>
                <p><strong>Telefone:</strong> <?php echo $tatuador['telefone']; ?></p>
                <img src="uploads/<?php echo $tatuador['foto_perfil']; ?>" alt="Foto do Tatuador">

                <!-- Carousel de Portfólio -->
                <?php
                // Buscar até 10 imagens do portfólio do tatuador
                $tatuador_id = $tatuador['id'];
                $sql_portfolio = "SELECT * FROM portfolio WHERE tatuador_id = $tatuador_id LIMIT 10";
                $portfolio_result = $conn->query($sql_portfolio);
                if ($portfolio_result->num_rows > 0) {
                    echo '<div id="carousel'.$tatuador['id'].'" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">';

                    // Variável para marcar a primeira imagem como ativa
                    $active_class = "active"; 
                    while ($portfolio = $portfolio_result->fetch_assoc()) {
                        echo '<div class="carousel-item ' . $active_class . '">
                                <img src="uploads/portfolio/' . $portfolio['imagem_url'] . '" class="d-block w-100" alt="Imagem do Portfólio">
                              </div>';
                        $active_class = ""; // Apenas a primeira imagem recebe a classe 'active'
                    }
                    echo '</div>
                          <button class="carousel-control-prev" type="button" data-bs-target="#carousel'.$tatuador['id'].'" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                          </button>
                          <button class="carousel-control-next" type="button" data-bs-target="#carousel'.$tatuador['id'].'" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                          </button>
                        </div>';
                }
                ?>
                <p><a href="detalhes.php?id=<?php echo $tatuador['id']; ?>">Ver Portfólio Completo</a></p>
            </div>
        <?php } ?>
    </div>

</body>
</html>

<?php
$conn->close();
?>
