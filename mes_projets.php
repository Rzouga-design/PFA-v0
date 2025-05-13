<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get filter status if set
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Prepare the SQL query to fetch ALL projects
$sql = "SELECT * FROM projets";
if ($status_filter !== 'all') {
    $sql .= " WHERE statut = ?";
}

$stmt = $conn->prepare($sql);

if ($status_filter !== 'all') {
    $stmt->bind_param("s", $status_filter);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tous les Projets - Plateforme Projets Militaires</title>
    <style>
        :root {
            --military-dark: #002040;
            --military-gold: #b09301;
            --military-light: #f0f0f0;
            --status-available: #2ecc71;
            --status-reserved: #e74c3c;
            --status-ongoing: #3498db;
            --status-completed: #9b59b6;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .military-header {
            background-color: var(--military-dark);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .military-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--military-gold);
        }
        
        .military-btn {
            background-color: var(--military-gold);
            color: var(--military-dark);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .military-btn:hover {
            background-color: #d4a900;
            transform: translateY(-2px);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .filter-section {
            background-color: white;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .filter-form {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .filter-select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
            min-width: 200px;
        }
        
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .project-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 1.5rem;
            transition: transform 0.3s;
            border-top: 3px solid var(--military-gold);
        }
        
        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .project-status {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: white;
        }
        
        .status-disponible {
            background-color: var(--status-available);
        }
        
        .status-reserve {
            background-color: var(--status-reserved);
        }
        
        .status-en_cours {
            background-color: var(--status-ongoing);
        }
        
        .status-termine {
            background-color: var(--status-completed);
        }
        
        .project-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: var(--military-dark);
        }
        
        .project-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .project-badge {
            background-color: var(--military-light);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .project-description {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .no-projects {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            grid-column: 1 / -1;
        }
        
        .specialty-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background-color: var(--military-dark);
            color: white;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-right: 0.5rem;
        }
        
        .grade-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background-color: var(--military-gold);
            color: var(--military-dark);
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header class="military-header">
        <div class="military-title">PLATEFORME DE PROJETS MILITAIRES</div>
        <a href="encadrant_dashboard.php" class="military-btn">Tableau de bord</a>
    </header>

    <div class="container">
        <div class="filter-section">
            <form class="filter-form" method="GET" action="">
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>Tous les projets</option>
                    <option value="disponible" <?= $status_filter === 'disponible' ? 'selected' : '' ?>>Disponibles</option>
                    <option value="reserve" <?= $status_filter === 'reserve' ? 'selected' : '' ?>>Réservés</option>
                    <option value="en_cours" <?= $status_filter === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                    <option value="termine" <?= $status_filter === 'termine' ? 'selected' : '' ?>>Terminés</option>
                </select>
            </form>
        </div>

        <div class="projects-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($project = $result->fetch_assoc()): ?>
                    <div class="project-card">
                        <div class="project-status status-<?= htmlspecialchars($project['statut']) ?>">
                            <?= [
                                'disponible' => 'DISPONIBLE',
                                'reserve' => 'RÉSERVÉ',
                                'en_cours' => 'EN COURS',
                                'termine' => 'TERMINÉ'
                            ][$project['statut']] ?? strtoupper($project['statut']) ?>
                        </div>
                        
                        <h3 class="project-title"><?= htmlspecialchars($project['titre_projet']) ?></h3>
                        
                        <div class="project-meta">
                            <span class="specialty-badge">
                                <?= [
                                    'GC' => 'GÉNIE CIVIL',
                                    'TEL' => 'TÉLÉCOM',
                                    'EM' => 'ÉLECTROMÉCANIQUE',
                                    'GI' => 'GÉNIE INFO'
                                ][$project['specialite']] ?? $project['specialite'] ?>
                            </span>
                            
                            <span class="grade-badge">
                                <?= htmlspecialchars($project['grade']) ?>
                            </span>
                        </div>
                        
                        <div class="project-meta">
                            <span><strong>Encadrant:</strong> <?= htmlspecialchars($project['encadrant']) ?></span>
                            <span><strong>Élèves:</strong> <?= htmlspecialchars($project['nombre_eleves']) ?></span>
                        </div>
                        
                        <div class="project-description">
                            <p><strong>Objectif:</strong> <?= nl2br(htmlspecialchars($project['objectif'])) ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-projects">
                    <p>Aucun projet trouvé<?= $status_filter !== 'all' ? ' avec ce statut' : '' ?>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>