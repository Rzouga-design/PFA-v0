<!-- <?php
$host = 'localhost';
$dbname = 'plateforme_projets_militaires';
$username = 'root'; // Or your MySQL username
$password = ''; // Your MySQL password (empty if none)

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8");
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}
?> 