<?php
session_start();
require_once 'config.php'; // Make sure this file contains your database connection

// Check if user is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'eleve') {
    header("Location: login.php");
    exit();
}

// Verify student ID is set in session


// Initialize database connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur de connexion à la base de données: " . $e->getMessage();
    header("Location: dashboard_eleve.php");
    exit();
}

// Get student's reservations
$student_id = $_SESSION['id'];
$reservations = [];
try {
    $stmt = $pdo->prepare("
        SELECT r.*, p.titre_projet, p.specialite, p.nombre_eleves, p.encadrant, u.nom_unite 
        FROM reservations r
        JOIN projets p ON r.id_projet = p.id
        JOIN unites u ON p.id_unite = u.id
        WHERE r.id_eleve = :student_id
        ORDER BY r.date_candidature DESC
    ");
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération des réservations: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Candidatures - Plateforme Projets Militaires</title>
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
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .header h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .reservation-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s;
        }
        
        .reservation-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .reservation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .reservation-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .reservation-date {
            color: #666;
            font-size: 0.9rem;
        }
        
        .reservation-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .reservation-info strong {
            color: var(--primary-color);
            display: block;
            margin-bottom: 0.3rem;
        }
        
        .specialty-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            background-color: #e3f2fd;
            color: var(--secondary-color);
            font-weight: 500;
        }
        
        .admin-comment {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }
        
        .admin-comment h4 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .no-reservations {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
            text-align: center;
        }
        
        .no-reservations i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
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
        
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-danger {
            background-color: #fdecea;
            color: #c62828;
            border-left: 4px solid var(--accent-color);
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid var(--success-color);
        }
        
        @media (max-width: 768px) {
            .reservation-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .reservation-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-clipboard-list"></i> Mes Candidatures</h1>
            <a href="dashboard_eleve.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <?php 
                    echo htmlspecialchars($_SESSION['error_message']);
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <div>
                    <?php 
                    echo htmlspecialchars($_SESSION['success_message']);
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($reservations)): ?>
            <div class="no-reservations">
                <i class="fas fa-inbox"></i>
                <h3>Aucune candidature trouvée</h3>
                <p>Vous n'avez pas encore postulé à des projets.</p>
                <a href="projets_disponibles.php" class="btn btn-primary">
                    <i class="fas fa-search"></i> Voir les projets disponibles
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($reservations as $reservation): ?>
                <div class="reservation-card">
                    <div class="reservation-header">
                        <div class="reservation-title">
                            <?php echo htmlspecialchars($reservation['titre_projet']); ?>
                        </div>
                        <div>
                            <span class="status-badge 
                                <?php 
                                if ($reservation['statut'] === 'accepte') echo 'status-approved';
                                elseif ($reservation['statut'] === 'refuse') echo 'status-rejected';
                                else echo 'status-pending';
                                ?>">
                                <?php 
                                if ($reservation['statut'] === 'accepte') echo '<i class="fas fa-check-circle"></i> Accepté';
                                elseif ($reservation['statut'] === 'refuse') echo '<i class="fas fa-times-circle"></i> Refusé';
                                else echo '<i class="fas fa-clock"></i> En attente';
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="reservation-date">
                        <i class="far fa-calendar-alt"></i> Postulé le: 
                        <?php echo date('d/m/Y à H:i', strtotime($reservation['date_candidature'])); ?>
                    </div>
                    
                    <div class="reservation-details">
                        <div class="reservation-info">
                            <strong>Spécialité</strong>
                            <span class="specialty-badge">
                                <?php
                                $specialties = [
                                    'GC' => '<i class="fas fa-building"></i> Génie Civil',
                                    'TEL' => '<i class="fas fa-satellite-dish"></i> Télécommunication',
                                    'EM' => '<i class="fas fa-cogs"></i> Électromécanique',
                                    'GI' => '<i class="fas fa-laptop-code"></i> Génie Informatique'
                                ];
                                echo $specialties[$reservation['specialite']] ?? $reservation['specialite'];
                                ?>
                            </span>
                        </div>
                        
                        <div class="reservation-info">
                            <strong>Encadrant</strong>
                            <div><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($reservation['encadrant']); ?></div>
                        </div>
                        
                        <div class="reservation-info">
                            <strong>Nombre d'élèves</strong>
                            <div>
                                <?php if ($reservation['nombre_eleves'] === 'Monome'): ?>
                                    <i class="fas fa-user"></i> Monôme (1 élève)
                                <?php else: ?>
                                    <i class="fas fa-user-friends"></i> Binôme (2 élèves)
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="reservation-info">
                            <strong>Unité</strong>
                            <div><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($reservation['nom_unite']); ?></div>
                        </div>
                    </div>
                    
                    <?php if (!empty($reservation['commentaire'])): ?>
                        <div class="reservation-info">
                            <strong>Votre commentaire</strong>
                            <p><?php echo nl2br(htmlspecialchars($reservation['commentaire'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($reservation['statut'] !== 'en_attente' && !empty($reservation['admin_comment'])): ?>
                        <div class="admin-comment">
                            <h4><i class="fas fa-comment"></i> Commentaire de l'administrateur</h4>
                            <p><?php echo nl2br(htmlspecialchars($reservation['admin_comment'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>