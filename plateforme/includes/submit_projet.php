<?php
// php/submit_projet.php
require_once '../config.php';
session_start();

// Vérifier que l'utilisateur est connecté comme encadrant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'encadrant') {
    header('Location: ../index.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupération des données du formulaire
        $data = [
            ':titre' => $_POST['titre'],
            ':specialite' => $_POST['specialite'],
            ':nombre_eleves' => $_POST['nombreEleves'],
            ':encadrant' => $_POST['encadrant'],
            ':organisme' => $_POST['organisme'],
            ':grade' => $_POST['grade'],
            ':telephone' => $_POST['telephone'],
            ':email' => $_POST['email'],
            ':description' => $_POST['description'],
            ':objectif' => $_POST['objectif'],
            ':resultats' => $_POST['resultats'],
            ':id_unite' => $_SESSION['user_id']
        ];

        // Requête d'insertion
        $sql = "INSERT INTO projets (
            titre_projet, specialite, nombre_eleves, encadrant, 
            organisme_adresse, grade, telephone, email, 
            description, objectif, resultats_attendus, id_unite
        ) VALUES (
            :titre, :specialite, :nombre_eleves, :encadrant,
            :organisme, :grade, :telephone, :email,
            :description, :objectif, :resultats, :id_unite
        )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);

        // Redirection après succès
        header('Location: ../encadrant/dashboard.php?success=1');
        exit;
    } catch (PDOException $e) {
        die("Erreur lors de l'enregistrement du projet: " . $e->getMessage());
    }
}

header('Location: ../PropEncadrant.html');
exit;
?>