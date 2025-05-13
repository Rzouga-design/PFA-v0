<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate that all required fields are present
    if (!isset($_POST['nom_unite']) || !isset($_POST['role']) || !isset($_POST['password']) ||
        empty($_POST['nom_unite']) || empty($_POST['role']) || empty($_POST['password'])) {
        $error = "Veuillez remplir tous les champs requis.";
    } else {
        $nom_unite = $_POST['nom_unite'];
        $role = $_POST['role'];
        $password = $_POST['password'];
        
        // Hash the password using SHA2
        $hashed_password = hash('sha256', $password);
        
        $sql = "SELECT * FROM unites WHERE nom_unite = ? AND role = ? AND mot_de_passe = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nom_unite, $role, $hashed_password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom_unite'] = $user['nom_unite'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            switch($user['role']) {
                case 'eleve':
                    header("Location: eleve_dashboard.php");
                    break;
                case 'encadrant':
                    header("Location: encadrant_dashboard.php");
                    break;
                case 'admin':
                    header("Location: admin_dashboard.php");
                    break;
            }
            exit();
        } else {
            $error = "Invalid credentials";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Plateforme Projets Militaires</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
    <style>
        :root {
            --primary-color: #1a3e72;
            --secondary-color: #2c5282;
            --accent-color: #4a7bc9;
            --error-color: #e53e3e;
            --success-color: #38a169;
            --light-gray: #f7fafc;
            --dark-gray: #2d3748;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color:rgb(254, 254, 254);
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e4e8ed 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 450px;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }
        
        h2 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 500;
            font-size: 1.8rem;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .logo img {
            height: 100px;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        select, input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.2s;
            background-color: white;
        }
        
        select:focus, input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(74, 123, 201, 0.2);
        }
        
        select {
            appearance: none;
            background-image: ("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1em;
        }
        
        .password-container {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--dark-gray);
            cursor: pointer;
        }
        
        button[type="submit"] {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 0.5rem;
        }
        
        button[type="submit"]:hover {
            background: linear-gradient(to right, var(--secondary-color), var(--accent-color));
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        button[type="submit"]:active {
            transform: translateY(0);
        }
        
        .error {
            color: var(--error-color);
            background-color: rgba(229, 62, 62, 0.1);
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            border-left: 4px solid var(--error-color);
        }
        
        .footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #718096;
            font-size: 0.85rem;
        }
        
        .footer a {
            color: var(--accent-color);
            text-decoration: none;
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 1.5rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <!-- Replace with your actual logo -->
            <img src="logodef.png" alt="Plateforme Projets Militaires">
        </div>
        
        <h2>Connexion</h2>
        
        <?php if (isset($error)) echo "<p class='error'><strong>Erreur :</strong> $error</p>"; ?>
        
        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="nom_unite">Unité militaire</label>
                <select id="nom_unite" name="nom_unite" required>
                    <option value="">Sélectionnez une unité</option>
                    <option value="Académie militaire">Académie militaire</option>
                    <option value="Hôpital militaire principal d'instruction de Tunis">Hôpital militaire principal d'instruction de Tunis</option>
                    <option value="D.G.T.I">D.G.T.I</option>
                    <option value="D.G.M.A">D.G.M.A</option>
                    <option value="Régiment 71 transmission">Régiment 71 transmission</option>
                    <option value="D.G.M.R.E">D.G.M.R.E</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="role">Profil</label>
                <select id="role" name="role" required>
                    <option value="">Sélectionnez d'abord une unité</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required>
                    <button type="button" class="toggle-password" aria-label="Afficher le mot de passe">👁️</button>
                </div>
            </div>
            
            <button type="submit">Accéder à la plateforme</button>
        </form>
        
        <div class="footer">
            <p>Plateforme sécurisée | <a href="#">Aide</a> | <a href="#">Contact support</a></p>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const unitSelect = document.getElementById('nom_unite');
            const roleSelect = document.getElementById('role');
            const togglePassword = document.querySelector('.toggle-password');
            const passwordInput = document.getElementById('password');
            
            // Define available roles for each unit
            const unitRoles = {
                'Académie militaire': ['eleve', 'encadrant', 'admin'],
                'Hôpital militaire principal d\'instruction de Tunis': ['encadrant'],
                'D.G.T.I': ['encadrant'],
                'D.G.M.A': ['encadrant'],
                'Régiment 71 transmission': ['encadrant'],
                'D.G.M.R.E': ['encadrant']
            };
            
            // Role display names
            const roleDisplayNames = {
                'eleve': 'Élève',
                'encadrant': 'Encadrant',
                'admin': 'Administrateur'
            };
            
            // Function to update role options based on selected unit
            function updateRoleOptions() {
                const selectedUnit = unitSelect.value;
                roleSelect.innerHTML = ''; // Clear current options
                
                if (!selectedUnit) {
                    // If no unit is selected, show default message
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = 'Sélectionnez d\'abord une unité';
                    roleSelect.appendChild(defaultOption);
                    roleSelect.disabled = true;
                    return;
                }
                
                roleSelect.disabled = false;
                
                // Add available roles for the selected unit
                const roles = unitRoles[selectedUnit];
                roles.forEach(role => {
                    const option = document.createElement('option');
                    option.value = role;
                    option.textContent = roleDisplayNames[role];
                    roleSelect.appendChild(option);
                });
            }
            
            // Toggle password visibility
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.textContent = type === 'password' ? '👁️' : '🔒';
            });
            
            // Add event listener for unit selection change
            unitSelect.addEventListener('change', updateRoleOptions);
            
            // Initial role options update
            updateRoleOptions();
            
            // Form submission animation
            const loginForm = document.getElementById('loginForm');
            loginForm.addEventListener('submit', function(e) {
                const submitButton = this.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner"></span> Vérification...';
            });
        });
    </script>
</body>
</html>