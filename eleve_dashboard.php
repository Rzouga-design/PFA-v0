<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is an eleve
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'eleve') {
    header("Location: index.php");
    exit();
}

// Get student's active reservations count
try {
    $reservations_sql = "SELECT COUNT(*) as active_count 
                        FROM reservations r 
                        JOIN projets p ON r.id_projet = p.id 
                        WHERE r.id_eleve = ? AND p.statut IN ('reserve', 'en_cours')";
    $reservations_stmt = $conn->prepare($reservations_sql);
    $reservations_stmt->bind_param("i", $_SESSION['user_id']);
    $reservations_stmt->execute();
    $active_reservations = $reservations_stmt->get_result()->fetch_assoc()['active_count'];
} catch (mysqli_sql_exception $e) {
    // If the table doesn't exist yet, set count to 0
    $active_reservations = 0;
}

// Get available projects count
$projects_sql = "SELECT COUNT(*) as available_count 
                FROM projets 
                WHERE statut = 'disponible'";
$available_projects = $conn->query($projects_sql)->fetch_assoc()['available_count'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Élève - Plateforme Projets Militaires</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .header h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
        }
        
        .user-badge {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-icon {
            background-color: var(--secondary-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .welcome-message {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        
        .welcome-message h2 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            transition: all 0.3s ease;
            border-top: 4px solid var(--secondary-color);
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .card-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
            color: var(--secondary-color);
        }
        
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .card-content {
            color: #666;
            margin-bottom: 1.5rem;
            min-height: 60px;
        }
        
        .card-stats {
            font-size: 2.2rem;
            font-weight: bold;
            color: var(--secondary-color);
            margin: 0.5rem 0;
        }
        
        .stats-label {
            color: #777;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.7rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            width: 100%;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        
        .btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #27ae60;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #e67e22;
        }
        
        .logout-btn {
            background-color: var(--accent-color);
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .logout-btn:hover {
            background-color: #c0392b;
        }
        
        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--accent-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .card-actions {
            display: flex;
            gap: 10px;
            margin-top: 1rem;
        }
        
        .card-actions .btn {
            width: auto;
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-graduation-cap"></i> Tableau de Bord Élève</h1>
            <div class="user-badge">
                <div class="user-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <strong><?php echo htmlspecialchars($_SESSION['nom_unite']); ?></strong>
                    <div>Élève</div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>

        <div class="welcome-message">
            <h2><i class="fas fa-hand-wave"></i> Bienvenue!</h2>
            <p>Vous êtes connecté en tant qu'élève. Utilisez ce tableau de bord pour explorer les projets disponibles, gérer vos candidatures et suivre votre progression.</p>
        </div>

        <div class="dashboard-grid">
            <!-- Projets disponibles -->
            <div class="dashboard-card">
                <div class="card-header">
                    <i class="fas fa-search card-icon"></i>
                    <h3 class="card-title">Projets disponibles</h3>
                </div>
                <div class="card-content">
                    <p>Explorez la liste des projets disponibles correspondant à votre spécialité.</p>
                    <div class="card-stats"><?php echo $available_projects; ?></div>
                    <div class="stats-label">projets ouverts aux candidatures</div>
                </div>
                <div class="card-actions">
                    <a href="projets_disponibles.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> Voir la liste
                    </a>
                    <a href="recherche_projets.php" class="btn btn-secondary">
                        <i class="fas fa-filter"></i> Filtrer
                    </a>
                </div>
            </div>

            <!-- Mes candidatures -->
            <div class="dashboard-card">
                <div class="card-header">
                    <!-- <div style="position: relative;">
                        <i class="fas fa-clipboard-list card-icon"></i>
                        <?php if ($pending_applications > 0): ?>
                            <span class="notification-badge"><?php echo $pending_applications; ?></span>
                        <?php endif; ?>
                    </div> -->
                    <h3 class="card-title">Mes candidatures</h3>
                </div>
                <div class="card-content">
                    <p>Suivez l'état de vos candidatures et projets en cours.</p>
                    <div class="card-stats"><?php echo $active_reservations; ?></div>
                    <div class="stats-label">projets actifs</div>
                </div>
                <a href="mes_candidatures.php" class="btn btn-primary">
                    <i class="fas fa-eye"></i> Voir mes candidatures
                </a>
            </div>

            <!-- Mon profil -->
            <div class="dashboard-card">
                <div class="card-header">
                    <i class="fas fa-user-graduate card-icon"></i>
                    <h3 class="card-title">Mon profil</h3>
                </div>
                <div class="card-content">
                    <p>Gérez vos informations personnelles, vos compétences et vos préférences.</p>
                    <div style="margin-top: 1rem;">
                        <div style="font-size: 0.9rem; color: #555;">
                            <i class="fas fa-shield-alt"></i> Spécialité: <?php echo htmlspecialchars($_SESSION['specialite']); ?>
                        </div>
                    </div>
                </div>
                <div class="card-actions">
                    <a href="profil_eleve.php" class="btn btn-secondary">
                        <i class="fas fa-cog"></i> Modifier
                    </a>
                    <a href="competences.php" class="btn btn-success">
                        <i class="fas fa-award"></i> Compétences
                    </a>
                </div>
            </div>

            <!-- Calendrier & Échéances -->
            <div class="dashboard-card">
                <div class="card-header">
                    <i class="fas fa-calendar-alt card-icon"></i>
                    <h3 class="card-title">Calendrier</h3>
                </div>
                <div class="card-content">
                    <p>Consultez les échéances importantes et les dates clés de vos projets.</p>
                    <?php if ($upcoming_deadlines > 0): ?>
                        <div style="color: var(--accent-color); font-weight: bold; margin-top: 0.5rem;">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $upcoming_deadlines; ?> échéance(s) proche(s)
                        </div>
                    <?php else: ?>
                        <div style="color: var(--success-color); margin-top: 0.5rem;">
                            <i class="fas fa-check-circle"></i> Aucune échéance imminente
                        </div>
                    <?php endif; ?>
                </div>
                <a href="calendrier.php" class="btn btn-warning">
                    <i class="fas fa-calendar-check"></i> Voir calendrier
                </a>
            </div>
        </div>
    </div>
</body>
</html>