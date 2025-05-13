<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is an encadrant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'encadrant') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Encadrant - Plateforme Projets Militaires</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --light-bg: #f8f9fa;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            color: #333;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-icon {
            width: 50px;
            height: 50px;
            background-color: var(--secondary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }
        
        .user-details h2 {
            color: var(--primary-color);
            margin-bottom: 0.3rem;
        }
        
        .user-role {
            background-color: var(--primary-color);
            color: white;
            padding: 0.2rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            display: inline-block;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background-color: var(--accent-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }
        
        .stats-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            flex: 1;
            min-width: 200px;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--secondary-color);
            margin: 0.5rem 0;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .feature-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border-top: 4px solid var(--secondary-color);
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .feature-card h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .feature-icon {
            color: var(--secondary-color);
            font-size: 1.5rem;
        }
        
        .feature-card p {
            color: #666;
            margin-bottom: 1.5rem;
            min-height: 60px;
        }
        
        .card-footer {
            display: flex;
            justify-content: flex-end;
        }
        
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1.5rem;
            }
            
            .action-buttons {
                width: 100%;
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="user-info">
                <div class="user-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="user-details">
                    <h2><?php echo htmlspecialchars($_SESSION['nom_unite']); ?></h2>
                    <span class="user-role">
                        <i class="fas fa-shield-alt"></i> <?php echo htmlspecialchars($_SESSION['role']); ?>
                    </span>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="proposer_projet.php" class="btn btn-success">
                    <i class="fas fa-plus-circle"></i> Proposer un Projet
                </a>
                <a href="logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
        
        <div class="stats-bar">
            <!-- <div class="stat-card">
                <div class="stat-label">Projets proposés</div>
                <div class="stat-value"><?php echo $projets_proposes; ?></div>
                <a href="mes_projets.php">Voir détails</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Projets réservés</div>
                <div class="stat-value"><?php echo $projets_reserves; ?></div>
                <a href="projets_reserves.php">Voir détails</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Candidatures en attente</div>
                <div class="stat-value"><?php echo $candidatures_attente; ?></div>
                <a href="candidatures.php">Voir détails</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Élèves encadrés</div>
                <div class="stat-value"><?php echo $eleves_encadres; ?></div>
                <a href="mes_eleves.php">Voir détails</a>
            </div> -->
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <h3><i class="fas fa-lightbulb feature-icon"></i> Proposer un Projet</h3>
                <p>Soumettez un nouveau projet avec toutes les spécifications nécessaires pour les élèves. Remplissez le formulaire détaillé avec les objectifs, résultats attendus et compétences requises.</p>
                <div class="card-footer">
                    <a href="proposer_projet.php" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Accéder
                    </a>
                </div>
            </div>
            
            <div class="feature-card">
                <h3><i class="fas fa-list-ol feature-icon"></i> Mes Projets</h3>
                <p>Consultez l'ensemble de vos projets proposés, avec leur statut actuel. Filtrez par projets disponibles, réservés ou terminés. Modifiez les projets non encore réservés.</p>
                <div class="card-footer">
                    <a href="mes_projets.php" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Accéder
                    </a>
                </div>
            </div>
            
            <div class="feature-card">
                <h3><i class="fas fa-tasks feature-icon"></i> Gérer les Projets</h3>
                <p>Modifiez ou supprimez vos projets non réservés. Pour les projets réservés, suivez la progression des élèves et fournissez vos retours et évaluations.</p>
                <div class="card-footer">
                    <a href="gerer_projet.php" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Accéder
                    </a>
                </div>
            </div>
            
            <div class="feature-card">
                <h3><i class="fas fa-users feature-icon"></i> Candidatures</h3>
                <p>Consultez et gérez les candidatures des élèves pour vos projets. Acceptez ou refusez les demandes en fonction des compétences et motivations des candidats.</p>
                <div class="card-footer">
                    <a href="candidatures.php" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Accéder
                    </a>
                </div>
            </div>
            
            <div class="feature-card">
                <h3><i class="fas fa-chart-line feature-icon"></i> Suivi des Projets</h3>
                <p>Suivez l'avancement des projets que vous encadrez. Consultez les rapports d'étape, fournissez des feedbacks et évaluez le travail des élèves.</p>
                <div class="card-footer">
                    <a href="suivi_projets.php" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Accéder
                    </a>
                </div>
            </div>
            
            <div class="feature-card">
                <h3><i class="fas fa-file-alt feature-icon"></i> Rapports</h3>
                <p>Générez des rapports sur vos activités d'encadrement. Exportez les données pour analyse ou pour vos archives personnelles.</p>
                <div class="card-footer">
                    <a href="rapports.php" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Accéder
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>