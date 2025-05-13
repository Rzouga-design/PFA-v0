<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is an eleve
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'eleve') {
    header("Location: index.php");
    exit();
}

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$specialty_filter = isset($_GET['specialty']) ? $_GET['specialty'] : 'all';

// Prepare the SQL query with search and filters
$sql = "SELECT p.*, u.nom_unite 
        FROM projets p 
        JOIN unites u ON p.id_unite = u.id 
        WHERE p.statut = 'disponible'";

$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (p.titre_projet LIKE ? OR p.description LIKE ? OR p.objectif LIKE ? OR p.technologies LIKE ?)";
    $search_param = "%$search%";
    $params = array_fill(0, 4, $search_param);
    $types = "ssss";
}

if ($specialty_filter !== 'all') {
    $sql .= " AND p.specialite = ?";
    $params[] = $specialty_filter;
    $types .= "s";
}

$sql .= " ORDER BY p.titre_projet ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projets Disponibles - Plateforme Projets Militaires</title>
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
            position: relative;
        }

        .project-card:hover {
            transform: translateY(-5px);
        }

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

        .project-details {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .project-details h4 {
            color: #333;
            margin-bottom: 8px;
        }

        .project-details p {
            color: #666;
            margin-bottom: 12px;
            font-size: 0.9em;
        }

        .btn-reserve {
            background-color: #007bff;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
            transition: background-color 0.3s;
        }

        .btn-reserve:hover {
            background-color: #0056b3;
        }

        .level-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 5px;
        }

        .level-debutant { background-color: #d4edda; color: #155724; }
        .level-intermediaire { background-color: #fff3cd; color: #856404; }
        .level-avance { background-color: #cce5ff; color: #004085; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Projets Disponibles</h1>
            <div>
                <a href="eleve_dashboard.php" class="btn btn-secondary">Retour au tableau de bord</a>
            </div>
        </div>

        <div class="search-filters">
            <form class="search-form" method="GET" action="">
                <div class="form-group">
                    <label for="search">Rechercher un projet :</label>
                    <input type="text" id="search" name="search" class="search-input" 
                           placeholder="Titre, description, objectif ou technologies..." 
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
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </div>
            </form>
        </div>

        <?php if (!empty($search) || $specialty_filter !== 'all'): ?>
            <div class="search-results-info">
                <?php
                $filters = [];
                if (!empty($search)) $filters[] = "recherche : \"$search\"";
                if ($specialty_filter !== 'all') {
                    $specialties = ['GC' => 'Génie Civil', 'TEL' => 'Télécommunication', 'EM' => 'Électromécanique', 'GI' => 'Génie Informatique'];
                    $filters[] = "spécialité : " . $specialties[$specialty_filter];
                }
                echo "Filtres appliqués : " . implode(", ", $filters);
                ?>
            </div>
        <?php endif; ?>

        <div class="projects-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($project = $result->fetch_assoc()): ?>
                    <div class="project-card">
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

                        <div class="project-details">
                            <h4>Objectif</h4>
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

                            <?php if (!empty($project['technologies'])): ?>
                            <h4>Technologies requises</h4>
                            <p>
                                <?php 
                                if (!empty($search)) {
                                    echo preg_replace('/(' . preg_quote($search, '/') . ')/i', 
                                        '<span class="highlight">$1</span>', 
                                        nl2br(htmlspecialchars($project['technologies'])));
                                } else {
                                    echo nl2br(htmlspecialchars($project['technologies']));
                                }
                                ?>
                            </p>
                            <?php endif; ?>

                            <?php if (!empty($project['niveau_requis'])): ?>
                            <h4>Niveau requis</h4>
                            <p>
                                <span class="level-badge level-<?php echo strtolower($project['niveau_requis']); ?>">
                                    <?php echo htmlspecialchars($project['niveau_requis']); ?>
                                </span>
                            </p>
                            <?php endif; ?>
                        </div>

                        <div class="project-unit">
                            <strong>Unité:</strong> <?php echo htmlspecialchars($project['nom_unite']); ?>
                        </div>

                        <a href="reserver_projet.php?id=<?php echo $project['id']; ?>" class="btn-reserve">
                            Réserver ce projet
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-projects">
                    <p>Aucun projet disponible<?php 
                        if (!empty($search) || $specialty_filter !== 'all') {
                            echo ' avec les filtres actuels';
                        }
                    ?>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add Font Awesome for icons
        const fontAwesome = document.createElement('link');
        fontAwesome.rel = 'stylesheet';
        fontAwesome.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css';
        document.head.appendChild(fontAwesome);
    </script>
</body>
</html> 