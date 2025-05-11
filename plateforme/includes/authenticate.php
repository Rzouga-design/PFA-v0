<?php
session_start();
require_once 'config.php';

// Debug: Log all received data
error_log("=== NEW REQUEST ===");
error_log("POST data: " . print_r($_POST, true));
error_log("SERVER data: " . print_r($_SERVER, true));

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    error_log("Form was properly submitted via POST");

    $unite = $_POST['unite'] ?? null;
    $role = $_POST['role'] ?? null;
    $password = $_POST['password'] ?? null;

    if (!$unite || !$role) {
        error_log("Missing required fields: unite=" . $unite . " role=" . $role);
        header('Location: ../login/login.php?error=missing_fields');
        exit;
    }

    try {
        error_log("Attempting database query for: unite=" . $unite . " role=" . $role);

        $stmt = $conn->prepare("SELECT * FROM unites WHERE nom_unite = :unite AND role = :role");
        $stmt->execute([':unite' => $unite, ':role' => $role]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            error_log("User found: " . print_r($user, true));
            $hashedInput = hash('sha256', $password);

            if ($hashedInput === $user['mot_de_passe']) {
                error_log("Password matches - authentication successful");
                $_SESSION['user'] = $user;

                // Redirect based on role
                switch ($role) {
                    case 'admin':
                        header('Location: ../admin.html');
                        break;
                    case 'encadrant':
                        header('Location: ../PropEncadrant.html');
                        break;
                    case 'eleve':
                        header('Location: ../eleve.html');
                        break;
                    default:
                        header('Location: ../login/login.php?error=invalid_role');
                        break;
                }
                exit;
            } else {
                error_log("Password mismatch: input hash=$hashedInput, db hash=" . $user['mot_de_passe']);
                header('Location: ../login/login.php?error=invalid_credentials');
                exit;
            }
        } else {
            error_log("No user found with these credentials");
            header('Location: ../login/login.php?error=invalid_credentials');
            exit;
        }

    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header('Location: ../login/login.php?error=database_error');
        exit;
    }
}

// If we get here, something was wrong with the request
error_log("Invalid request - no POST data or wrong method");
header('Location: ../login/login.php?error=invalid_request');
exit;
?>