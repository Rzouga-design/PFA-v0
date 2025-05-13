<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is an eleve
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'eleve') {
    header("Location: index.php");
    exit();
}

// Check if project ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Aucun projet spécifié.";
    header("Location: projets_disponibles.php");
    exit();
}

$project_id = $_GET['id'];
$eleve_id = $_SESSION['user_id'];

// Get project details
$sql = "SELECT p.*, u.nom_unite 
        FROM projets p 
        JOIN unites u ON p.id_unite = u.id 
        WHERE p.id = ? AND p.statut = 'disponible'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Projet non trouvé ou non disponible.";
    header("Location: projets_disponibles.php");
    exit();
}

$project = $result->fetch_assoc();

// Check if student already has a reservation for this project
$check_sql = "SELECT id FROM reservations WHERE id_projet = ? AND id_eleve = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $project_id, $eleve_id);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows > 0) {
    $_SESSION['error_message'] = "Vous avez déjà soumis une candidature pour ce projet.";
    header("Location: projets_disponibles.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commentaire = trim($_POST['commentaire'] ?? '');
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert reservation
        $insert_sql = "INSERT INTO reservations (id_projet, id_eleve, commentaire) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iis", $project_id, $eleve_id, $commentaire);
        
        if ($insert_stmt->execute()) {
            // Update project status to 'reserve'
            $update_sql = "UPDATE projets SET statut = 'reserve' WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $project_id);
            
            if ($update_stmt->execute()) {
                $conn->commit();
                $_SESSION['success_message'] = "Votre candidature a été soumise avec succès.";
                header("Location: mes_candidatures.php");
                exit();
            } else {
                throw new Exception("Erreur lors de la mise à jour du statut du projet.");
            }
        } else {
            throw new Exception("Erreur lors de la soumission de votre candidature.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver un Projet - Plateforme Projets Militaires</title>
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
            max-width: 900px;
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
        
        .project-details {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .project-title {
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .project-title i {
            color: var(--secondary-color);
        }
        
        .project-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .project-info {
            margin-bottom: 0.5rem;
        }
        
        .project-info strong {
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
        
        .project-description {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }
        
        .project-description p {
            margin-bottom: 1rem;
            color: #555;
        }
        
        .confirmation-box {
            background-color: #fff8e1;
            border-left: 4px solid var(--warning-color);
            padding: 1.5rem;
            border-radius: 6px;
            margin-bottom: 2rem;
        }
        
        .confirmation-box h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .confirmation-box h3 i {
            color: var(--warning-color);
        }
        
        .confirmation-box ul {
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .confirmation-box li {
            margin-bottom: 0.5rem;
        }
        
        .reservation-form {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #7f8c8d;
            transform: translateY(-2px);
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
        
        .character-count {
            font-size: 0.8rem;
            color: #777;
            text-align: right;
            margin-top: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .project-info-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-calendar-check"></i> Réserver un Projet</h1>
            <a href="projets_disponibles.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour aux projets
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

        <div class="project-details">
            <div class="project-title">
                <i class="fas fa-project-diagram"></i>
                <?php echo htmlspecialchars($project['titre_projet']); ?>
            </div>
            
            <div class="project-info-grid">
                <div class="project-info">
                    <strong>Spécialité</strong>
                    <span class="specialty-badge">
                        <?php
                        $specialties = [
                            'GC' => '<i class="fas fa-building"></i> Génie Civil',
                            'TEL' => '<i class="fas fa-satellite-dish"></i> Télécommunication',
                            'EM' => '<i class="fas fa-cogs"></i> Électromécanique',
                            'GI' => '<i class="fas fa-laptop-code"></i> Génie Informatique'
                        ];
                        echo $specialties[$project['specialite']] ?? $project['specialite'];
                        ?>
                    </span>
                </div>

                <div class="project-info">
                    <strong>Encadrant</strong>
                    <div><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($project['encadrant']); ?></div>
                </div>

                <div class="project-info">
                    <strong>Nombre d'élèves</strong>
                    <div>
                        <?php if ($project['nombre_eleves'] === 'Monome'): ?>
                            <i class="fas fa-user"></i> Monôme (1 élève)
                        <?php else: ?>
                            <i class="fas fa-user-friends"></i> Binôme (2 élèves)
                        <?php endif; ?>
                    </div>
                </div>

                <div class="project-info">
                    <strong>Unité</strong>
                    <div><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($project['nom_unite']); ?></div>
                </div>
            </div>

            <div class="project-description">
                <div class="project-info">
                    <strong><i class="fas fa-bullseye"></i> Objectif</strong>
                    <p><?php echo nl2br(htmlspecialchars($project['objectif'])); ?></p>
                </div>

                <?php if (!empty($project['technologies'])): ?>
                <div class="project-info">
                    <strong><i class="fas fa-tools"></i> Technologies requises</strong>
                    <p><?php echo nl2br(htmlspecialchars($project['technologies'])); ?></p>
                </div>
                <?php endif; ?>

                <div class="project-info">
                    <strong><i class="fas fa-clipboard-check"></i> Résultats attendus</strong>
                    <p><?php echo nl2br(htmlspecialchars($project['resultats_attendus'])); ?></p>
                </div>
            </div>
        </div>

        <div class="confirmation-box">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirmation de réservation</h3>
            <p>En soumettant votre candidature, vous confirmez que :</p>
            <ul>
                <li>Vous avez lu et compris les objectifs du projet</li>
                <li>Vous possédez les compétences requises pour ce projet</li>
                <li>Vous vous engagez à participer activement tout au long du projet</li>
                <li>Vous êtes disponible pendant la durée estimée du projet</li>
            </ul>
            <p><strong>Note :</strong> Votre candidature sera soumise à validation par l'encadrant.</p>
        </div>

        <div class="reservation-form">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="commentaire">
                        <i class="fas fa-comment-dots"></i> Lettre de motivation (optionnel)
                    </label>
                    <textarea id="commentaire" name="commentaire" class="form-control" 
                              placeholder="Expliquez pourquoi vous êtes intéressé par ce projet, comment vous comptez y contribuer, et quelles compétences vous apportez..."></textarea>
                    <div class="character-count"><span id="comment-count">0</span>/1000 caractères</div>
                </div>

                <div class="form-group">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Soumettre ma candidature
                        </button>
                        <a href="projets_disponibles.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Character counter for comment field
            const commentField = document.getElementById('commentaire');
            const counter = document.getElementById('comment-count');
            
            commentField.addEventListener('input', function() {
                counter.textContent = this.value.length;
                
                // Highlight if approaching limit
                if (this.value.length > 900) {
                    counter.style.color = '#e74c3c';
                    counter.style.fontWeight = 'bold';
                } else {
                    counter.style.color = '#777';
                    counter.style.fontWeight = 'normal';
                }
            });
            
            // Initialize counter
            counter.textContent = commentField.value.length;
        });
    </script>
</body>
</html>