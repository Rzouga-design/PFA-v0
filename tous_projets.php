<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is an encadrant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'encadrant') {
    header("Location: index.php");
    exit();
}

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$specialty_filter = isset($_GET['specialty']) ? $_GET['specialty'] : 'all';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Prepare the SQL query with search and filters
$sql = "SELECT p.*, u.nom_unite 
        FROM projets p 
        JOIN unites u ON p.id_unite = u.id 
        WHERE u.nom_unite = ?";

$params = [$_SESSION['nom_unite']];
$types = "s";

if (!empty($search)) {
    $sql .= " AND (p.titre_projet LIKE ? OR p.description LIKE ? OR p.objectif LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if ($specialty_filter !== 'all') {
    $sql .= " AND p.specialite = ?";
    $params[] = $specialty_filter;
    $types .= "s";
}

if ($status_filter !== 'all') {
    $sql .= " AND p.statut = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$sql .= " ORDER BY p.titre_projet ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tous les Projets - Plateforme Projets Militaires</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .search-filters {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .search-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .filter-select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }

        .btn-primary:hover {
            background-color: #45a049;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .project-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            transition: transform 0.2s;
        }

        .project-card:hover {
            transform: translateY(-5px);
        }

        .project-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .status-disponible { background-color: #d4edda; color: #155724; }
        .status-reserve { background-color: #fff3cd; color: #856404; }
        .status-en_cours { background-color: #cce5ff; color: #004085; }
        .status-termine { background-color: #d1ecf1; color: #0c5460; }

        .project-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .project-info {
            margin-bottom: 8px;
            color: #666;
        }

        .project-info strong {
            color: #333;
        }

        .project-unit {
            font-size: 0.9em;
            color: #666;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .project-actions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }

        .btn-manage {
            background-color: #007bff;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9em;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-manage:hover {
            background-color: #0056b3;
        }

        .btn-manage i {
            font-size: 1.1em;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .close-modal {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .close-modal:hover {
            color: #333;
        }

        .confirmation-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .specialty-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            background-color: #e9ecef;
            color: #495057;
            margin-top: 5px;
        }

        .highlight {
            background-color: #fff3cd;
            padding: 2px;
            border-radius: 2px;
        }

        .no-projects {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 8px;
            color: #666;
            grid-column: 1 / -1;
        }

        .search-results-info {
            margin-bottom: 20px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Tous les Projets</h1>
            <div>
                <a href="encadrant_dashboard.php" class="btn btn-secondary">Retour au tableau de bord</a>
            </div>
        </div>

        <div class="search-filters">
            <form class="search-form" method="GET" action="">
                <div class="form-group">
                    <label for="search">Rechercher un projet :</label>
                    <input type="text" id="search" name="search" class="search-input" 
                           placeholder="Titre, description ou objectif..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div class="form-group">
                    <label for="specialty">Spécialité :</label>
                    <select name="specialty" id="specialty" class="filter-select">
                        <option value="all">Toutes les spécialités</option>
                        <option value="GC" <?php echo $specialty_filter === 'GC' ? 'selected' : ''; ?>>Génie Civil</option>
                        <option value="TEL" <?php echo $specialty_filter === 'TEL' ? 'selected' : ''; ?>>Télécommunication</option>
                        <option value="EM" <?php echo $specialty_filter === 'EM' ? 'selected' : ''; ?>>Électromécanique</option>
                        <option value="GI" <?php echo $specialty_filter === 'GI' ? 'selected' : ''; ?>>Génie Informatique</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Statut :</label>
                    <select name="status" id="status" class="filter-select">
                        <option value="all">Tous les statuts</option>
                        <option value="disponible" <?php echo $status_filter === 'disponible' ? 'selected' : ''; ?>>Disponibles</option>
                        <option value="reserve" <?php echo $status_filter === 'reserve' ? 'selected' : ''; ?>>Réservés</option>
                        <option value="en_cours" <?php echo $status_filter === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                        <option value="termine" <?php echo $status_filter === 'termine' ? 'selected' : ''; ?>>Terminés</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </div>
            </form>
        </div>

        <?php if (!empty($search) || $specialty_filter !== 'all' || $status_filter !== 'all'): ?>
            <div class="search-results-info">
                <?php
                $filters = [];
                if (!empty($search)) $filters[] = "recherche : \"$search\"";
                if ($specialty_filter !== 'all') {
                    $specialties = ['GC' => 'Génie Civil', 'TEL' => 'Télécommunication', 'EM' => 'Électromécanique', 'GI' => 'Génie Informatique'];
                    $filters[] = "spécialité : " . $specialties[$specialty_filter];
                }
                if ($status_filter !== 'all') {
                    $statuses = ['disponible' => 'Disponibles', 'reserve' => 'Réservés', 'en_cours' => 'En cours', 'termine' => 'Terminés'];
                    $filters[] = "statut : " . $statuses[$status_filter];
                }
                echo "Filtres appliqués : " . implode(", ", $filters);
                ?>
            </div>
        <?php endif; ?>

        <div class="projects-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($project = $result->fetch_assoc()): ?>
                    <div class="project-card">
                        <div class="project-status status-<?php echo htmlspecialchars($project['statut']); ?>">
                            <?php
                            $status_display = [
                                'disponible' => 'Disponible',
                                'reserve' => 'Réservé',
                                'en_cours' => 'En cours',
                                'termine' => 'Terminé'
                            ];
                            echo $status_display[$project['statut']] ?? ucfirst($project['statut']);
                            ?>
                        </div>
                        
                        <div class="project-title">
                            <?php 
                            if (!empty($search)) {
                                echo preg_replace('/(' . preg_quote($search, '/') . ')/i', 
                                    '<span class="highlight">$1</span>', 
                                    htmlspecialchars($project['titre_projet']));
                            } else {
                                echo htmlspecialchars($project['titre_projet']);
                            }
                            ?>
                        </div>

                        <div class="project-info">
                            <strong>Spécialité:</strong>
                            <span class="specialty-badge">
                                <?php
                                $specialties = [
                                    'GC' => 'Génie Civil',
                                    'TEL' => 'Télécommunication',
                                    'EM' => 'Électromécanique',
                                    'GI' => 'Génie Informatique'
                                ];
                                echo $specialties[$project['specialite']] ?? $project['specialite'];
                                ?>
                            </span>
                        </div>

                        <div class="project-info">
                            <strong>Encadrant:</strong> <?php echo htmlspecialchars($project['encadrant']); ?>
                        </div>

                        <div class="project-info">
                            <strong>Nombre d'élèves:</strong> <?php echo htmlspecialchars($project['nombre_eleves']); ?>
                        </div>

                        <div class="project-objective">
                            <strong>Objectif:</strong>
                            <p>
                                <?php 
                                if (!empty($search)) {
                                    echo preg_replace('/(' . preg_quote($search, '/') . ')/i', 
                                        '<span class="highlight">$1</span>', 
                                        nl2br(htmlspecialchars($project['objectif'])));
                                } else {
                                    echo nl2br(htmlspecialchars($project['objectif']));
                                }
                                ?>
                            </p>
                        </div>

                        <div class="project-unit">
                            <strong>Unité:</strong> <?php echo htmlspecialchars($project['nom_unite']); ?>
                        </div>

                        <?php if ($project['id_unite'] === $_SESSION['user_id']): ?>
                        <div class="project-actions">
                            <a href="gerer_projet.php?id=<?php echo $project['id']; ?>" class="btn-manage">
                                <i class="fas fa-cog"></i> Gérer le projet
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-projects">
                    <p>Aucun projet trouvé<?php 
                        if (!empty($search) || $specialty_filter !== 'all' || $status_filter !== 'all') {
                            echo ' avec les filtres actuels';
                        }
                    ?>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Confirmer la suppression</h2>
            <p>Êtes-vous sûr de vouloir supprimer ce projet ? Cette action est irréversible.</p>
            <div class="confirmation-buttons">
                <button class="btn btn-secondary" onclick="closeModal()">Annuler</button>
                <button class="btn btn-danger" id="confirmDelete">Supprimer</button>
            </div>
        </div>
    </div>

    <script>
        // Add Font Awesome for icons
        const fontAwesome = document.createElement('link');
        fontAwesome.rel = 'stylesheet';
        fontAwesome.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css';
        document.head.appendChild(fontAwesome);

        // Modal functions
        function showDeleteModal(projectId) {
            const modal = document.getElementById('deleteModal');
            modal.style.display = 'block';
            document.getElementById('confirmDelete').onclick = function() {
                window.location.href = `gerer_projet.php?action=delete&id=${projectId}`;
            };
        }

        function closeModal() {
            const modal = document.getElementById('deleteModal');
            modal.style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Close modal when clicking the X
        document.querySelector('.close-modal').onclick = closeModal;
    </script>
</body>
</html> 