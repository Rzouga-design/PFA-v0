<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'encadrant') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id' => $_SESSION['user_id'],
        'titre' => $_POST['titre'],
        'specialite' => $_POST['specialite'],
        'nombre_eleves' => $_POST['nombreEleves'],
        'encadrant' => $_POST['encadrant'],
        'organisme' => $_POST['organisme'],
        'grade' => $_POST['grade'],
        'telephone' => $_POST['telephone'],
        'email' => $_POST['email'],
        'description' => $_POST['description'],
        'objectif' => $_POST['objectif'],
        'resultats_attendus' => $_POST['resultats']
    ];
    
    $sql = "INSERT INTO formulaire_projets 
            (user_id, titre, specialite, nombre_eleves, encadrant, organisme, grade, telephone, email, description, objectif, resultats_attendus) 
            VALUES 
            (:user_id, :titre, :specialite, :nombre_eleves, :encadrant, :organisme, :grade, :telephone, :email, :description, :objectif, :resultats_attendus)";
    
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($data);
        $success = "Projet enregistré avec succès!";
    } catch (PDOException $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}
?>

<!-- VOTRE CODE HTML EXISTANT -->
<?php if (isset($success)) echo "<p style='color:green'>$success</p>"; ?>
<?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>