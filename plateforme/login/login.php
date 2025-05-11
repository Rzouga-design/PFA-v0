<?php
session_start();

// Display error message if exists
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
    if ($error === 'invalid_credentials' || $error === 'missing_fields') {
        echo '<div class="error-message" style="color:red; padding:10px; margin:10px; border:1px solid #f00;">';
        echo match($error) {
            'invalid_credentials' => 'Mot de passe incorrect',
            'missing_fields' => 'Veuillez remplir tous les champs',
            default => 'Erreur de connexion'
        };
        echo '</div>';
    }
}
?>