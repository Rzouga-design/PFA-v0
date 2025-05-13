<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Plateforme Projets Militaires</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --light-color: #ecf0f1;
            --danger-color: #e74c3c;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .header h1 {
            color: var(--primary-color);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-icon {
            background-color: var(--accent-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .logout-btn {
            background-color: var(--danger-color);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s;
        }
        
        .logout-btn:hover {
            background-color: #c0392b;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            border-left: 4px solid var(--accent-color);
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        
        .dashboard-card h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .dashboard-card ul {
            list-style: none;
        }
        
        .dashboard-card li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .dashboard-card li:last-child {
            border-bottom: none;
        }
        
        .card-icon {
            color: var(--accent-color);
            font-size: 1.2rem;
        }
        
        .stats-container {
            margin-top: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--accent-color);
            margin: 0.5rem 0;
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <h1>Plateforme Projets Militaires</h1>
            <div class="user-info">
                <div class="user-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <!-- <div>
                    <strong><?php echo htmlspecialchars($_SESSION['nom_unite']); ?></strong>
                    <div>Administrateur</div>
                </div> -->
            </div>
        </div>
        
        <div class="stats-container">
            <h2><i class="fas fa-chart-bar card-icon"></i> Statistiques</h2>
            <div class="stats-grid">
                <!-- <div class="stat-card">
                    <div>Utilisateurs</div>
                    <div class="stat-number"><?php echo $user_count; ?></div>
                    <div><a href="users.php">Voir détails</a></div>
                </div>
                <div class="stat-card">
                    <div>Projets</div>
                    <div class="stat-number"><?php echo $project_count; ?></div>
                    <div><a href="projects.php">Voir détails</a></div>
                </div>
                <div class="stat-card">
                    <div>Réservations</div>
                    <div class="stat-number"><?php echo $reservation_count; ?></div>
                    <div><a href="reservations.php">Voir détails</a></div>
                </div>
                <div class="stat-card">
                    <div>Unités</div>
                    <div class="stat-number"><?php echo $unit_count; ?></div>
                    <div><a href="units.php">Voir détails</a></div>
                </div> -->
            </div>
        </div>
        
        <div class="dashboard-content">
            <h2><i class="fas fa-cogs card-icon"></i> Fonctionnalités administrateur</h2>
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3><i class="fas fa-users card-icon"></i> Gestion des utilisateurs</h3>
                    <ul>
                        <li><i class="fas fa-user-plus"></i> Créer un utilisateur</li>
                        <li><i class="fas fa-user-edit"></i> Modifier un utilisateur</li>
                        <li><i class="fas fa-user-times"></i> Supprimer un utilisateur</li>
                        <li><i class="fas fa-key"></i> Réinitialiser mot de passe</li>
                    </ul>
                </div>
                
                <div class="dashboard-card">
                    <h3><i class="fas fa-project-diagram card-icon"></i> Gestion des projets</h3>
                    <ul>
                        <li><i class="fas fa-plus-circle"></i> Ajouter un projet</li>
                        <li><i class="fas fa-edit"></i> Modifier un projet</li>
                        <li><i class="fas fa-trash-alt"></i> Supprimer un projet</li>
                        <li><i class="fas fa-check-circle"></i> Valider les projets</li>
                    </ul>
                </div>
                
                <div class="dashboard-card">
                    <h3><i class="fas fa-calendar-check card-icon"></i> Réservations</h3>
                    <ul>
                        <li><i class="fas fa-list"></i> Voir toutes les réservations</li>
                        <li><i class="fas fa-check"></i> Approuver une réservation</li>
                        <li><i class="fas fa-times"></i> Rejeter une réservation</li>
                        <li><i class="fas fa-envelope"></i> Notifier les élèves</li>
                    </ul>
                </div>
                
                <div class="dashboard-card">
                    <h3><i class="fas fa-chart-pie card-icon"></i> Rapports</h3>
                    <ul>
                        <li><i class="fas fa-file-alt"></i> Générer des rapports</li>
                        <li><i class="fas fa-download"></i> Exporter les données</li>
                        <li><i class="fas fa-filter"></i> Filtres avancés</li>
                        <li><i class="fas fa-history"></i> Historique des activités</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div style="text-align: right; margin-top: 2rem;">
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </div>
</body>
</html>