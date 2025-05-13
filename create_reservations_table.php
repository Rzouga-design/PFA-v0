<?php
require_once 'config.php';

// Create the reservations table
$sql = "CREATE TABLE IF NOT EXISTS reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_projet INT NOT NULL,
    id_eleve INT NOT NULL,
    date_candidature DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_attente', 'acceptee', 'refusee') DEFAULT 'en_attente',
    commentaire TEXT,
    FOREIGN KEY (id_projet) REFERENCES projets(id) ON DELETE CASCADE,
    FOREIGN KEY (id_eleve) REFERENCES unites(id) ON DELETE CASCADE,
    UNIQUE KEY unique_candidature (id_projet, id_eleve)
)";

if ($conn->query($sql)) {
    echo "Table 'reservations' créée avec succès.<br>";
    
    // Add index for better performance
    $index_sql = "CREATE INDEX idx_reservations_statut ON reservations(statut)";
    if ($conn->query($index_sql)) {
        echo "Index créé avec succès.<br>";
    } else {
        echo "Erreur lors de la création de l'index : " . $conn->error . "<br>";
    }
} else {
    echo "Erreur lors de la création de la table : " . $conn->error . "<br>";
}

// Modify the dashboard query to handle the case when the table doesn't exist yet
$modify_dashboard = "<?php
// In eleve_dashboard.php, replace the reservations query with this:
try {
    \$reservations_sql = \"SELECT COUNT(*) as active_count 
                        FROM reservations r 
                        JOIN projets p ON r.id_projet = p.id 
                        WHERE r.id_eleve = ? AND p.statut IN ('reserve', 'en_cours')\";
    \$reservations_stmt = \$conn->prepare(\$reservations_sql);
    \$reservations_stmt->bind_param(\"i\", \$_SESSION['user_id']);
    \$reservations_stmt->execute();
    \$active_reservations = \$reservations_stmt->get_result()->fetch_assoc()['active_count'];
} catch (mysqli_sql_exception \$e) {
    // If the table doesn't exist yet, set count to 0
    \$active_reservations = 0;
}
?>";

// Save the modified dashboard code
file_put_contents('eleve_dashboard_fix.php', $modify_dashboard);

echo "Code de correction pour eleve_dashboard.php généré dans 'eleve_dashboard_fix.php'.<br>";
echo "Veuillez remplacer le code de requête des réservations dans eleve_dashboard.php avec le code fourni dans eleve_dashboard_fix.php.";
?> 