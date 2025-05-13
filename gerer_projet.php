<?php
session_start();
require_once 'config.php';

// Debug: Check if we're reaching the start of the script
error_log("Script started - gerer_projet.php");

// Check if user is logged in and is an encadrant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'encadrant') {
    error_log("Unauthorized access attempt - redirecting to index.php");
    header("Location: index.php");
    exit();
}

// Check if project ID is provided
if (!isset($_GET['id'])) {
    error_log("No project ID provided - redirecting to tous_projets.php");
    header("Location: tous_projets.php");
    exit();
}

$project_id = $_GET['id'];
$encadrant_id = $_SESSION['user_id'];

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    error_log("Delete action requested for project ID: $project_id");
    
    // Verify that the project belongs to the current encadrant
    $check_sql = "SELECT id FROM projets WHERE id = ? AND id_unite = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $project_id, $encadrant_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Delete the project
        $delete_sql = "DELETE FROM projets WHERE id = ? AND id_unite = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $project_id, $encadrant_id);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success_message'] = "Le projet a été supprimé avec succès.";
            error_log("Project deleted successfully: $project_id");
        } else {
            $_SESSION['error_message'] = "Erreur lors de la suppression du projet.";
            error_log("Delete failed for project: $project_id");
        }
    } else {
        $_SESSION['error_message'] = "Vous n'êtes pas autorisé à supprimer ce projet.";
        error_log("Unauthorized delete attempt for project: $project_id");
    }
    
    header("Location: tous_projets.php");
    exit();
}

// Get project details - only if it belongs to the current encadrant
$sql = "SELECT * FROM projets WHERE id = ? AND id_unite = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $project_id, $encadrant_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    error_log("Project not found or unauthorized access: $project_id");
    $_SESSION['error_message'] = "Projet non trouvé ou vous n'avez pas l'autorisation.";
    header("Location: tous_projets.php");
    exit();
}

$project = $result->fetch_assoc();
error_log("Project loaded: " . print_r($project, true));

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Form submission received");
    
    $titre = trim($_POST['titre_projet']);
    $objectif = trim($_POST['objectif']);
    $specialite = $_POST['specialite'];
    $nombre_eleves = (int)$_POST['nombre_eleves'];
    $statut = $_POST['statut'];
    
    // Validate input
    $errors = [];
    if (empty($titre)) $errors[] = "Le titre est requis.";
    if (empty($objectif)) $errors[] = "L'objectif est requis.";
    if ($nombre_eleves < 1) $errors[] = "Le nombre d'élèves doit être au moins 1.";
    
    if (empty($errors)) {
        $update_sql = "UPDATE projets SET 
                      titre_projet = ?, 
                      objectif = ?, 
                      specialite = ?, 
                      nombre_eleves = ?,
                      statut = ?
                      WHERE id = ? AND id_unite = ?";
        
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssiiii", 
            $titre, 
            $objectif, 
            $specialite, 
            $nombre_eleves,
            $statut,
            $project_id,
            $encadrant_id
        );
        
        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "Le projet a été mis à jour avec succès.";
            error_log("Project updated successfully: $project_id");
            header("Location: tous_projets.php");
            exit();
        } else {
            $errors[] = "Erreur lors de la mise à jour du projet.";
            error_log("Update failed: " . $update_stmt->error);
        }
    } else {
        error_log("Validation errors: " . print_r($errors, true));
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer le Projet - Plateforme Projets Militaires</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Your existing CSS styles here */
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        /* ... rest of your CSS ... */
    </style>
</head>
<body>
    <?php 
    // Debug: Check if we're reaching the HTML output
    error_log("Rendering HTML output");
    ?>
    
    <div class="container">
        <div class="header">
            <h1>Gérer le Projet</h1>
            <a href="tous_projets.php" class="btn btn-secondary">Retour aux projets</a>
        </div>

        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo htmlspecialchars($_SESSION['success_message']);
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="titre_projet">Titre du projet :</label>
                    <input type="text" id="titre_projet" name="titre_projet" class="form-control" 
                           value="<?php echo htmlspecialchars($project['titre_projet']); ?>" required>
                </div>

                <!-- Rest of your form fields -->
                <div class="form-group">
                    <label for="objectif">Objectif :</label>
                    <textarea id="objectif" name="objectif" class="form-control" required><?php 
                        echo htmlspecialchars($project['objectif']); 
                    ?></textarea>
                </div>

                <div class="form-group">
                    <label for="specialite">Spécialité :</label>
                    <select id="specialite" name="specialite" class="form-control" required>
                        <option value="GC" <?php echo $project['specialite'] === 'GC' ? 'selected' : ''; ?>>Génie Civil</option>
                        <option value="TEL" <?php echo $project['specialite'] === 'TEL' ? 'selected' : ''; ?>>Télécommunication</option>
                        <option value="EM" <?php echo $project['specialite'] === 'EM' ? 'selected' : ''; ?>>Électromécanique</option>
                        <option value="GI" <?php echo $project['specialite'] === 'GI' ? 'selected' : ''; ?>>Génie Informatique</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="nombre_eleves">Nombre d'élèves :</label>
                    <input type="number" id="nombre_eleves" name="nombre_eleves" class="form-control" 
                           value="<?php echo htmlspecialchars($project['nombre_eleves']); ?>" 
                           min="1" required>
                </div>

                <div class="form-group">
                    <label for="statut">Statut :</label>
                    <select id="statut" name="statut" class="form-control" required>
                        <option value="disponible" <?php echo $project['statut'] === 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                        <option value="reserve" <?php echo $project['statut'] === 'reserve' ? 'selected' : ''; ?>>Réservé</option>
                        <option value="en_cours" <?php echo $project['statut'] === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                        <option value="termine" <?php echo $project['statut'] === 'termine' ? 'selected' : ''; ?>>Terminé</option>
                    </select>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    <button type="button" class="btn btn-danger" onclick="showDeleteModal(<?php echo $project_id; ?>)">Supprimer</button>
                </div>
            </form>
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

    <style>
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
            max-width: 500px;
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

        .confirmation-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
    </style>

    <script>
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

        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        document.querySelector('.close-modal').onclick = closeModal;
    </script>
</body>
</html>