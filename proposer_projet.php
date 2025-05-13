<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is an encadrant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'encadrant') {
    header("Location: index.php");
    exit();
}

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $titre = $_POST['titre'];
    $specialite = $_POST['specialite'];
    $nombre_eleves = $_POST['nombreEleves'];
    $encadrant = $_POST['encadrant'];
    $organisme = $_POST['organisme'];
    $grade = $_POST['grade'];
    $telephone = $_POST['telephone'];
    $email = $_POST['email'];
    $description = $_POST['description'];
    $objectif = $_POST['objectif'];
    $resultats = $_POST['resultats'];
    $id_unite = $_SESSION['user_id'];

    // Insert into database
    $sql = "INSERT INTO projets (
        titre_projet, 
        specialite, 
        nombre_eleves, 
        encadrant, 
        organisme_adresse, 
        grade, 
        telephone, 
        email, 
        description, 
        objectif, 
        resultats_attendus,
        id_unite,
        statut
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'disponible')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssi", 
        $titre, 
        $specialite, 
        $nombre_eleves, 
        $encadrant, 
        $organisme, 
        $grade, 
        $telephone, 
        $email, 
        $description, 
        $objectif, 
        $resultats,
        $id_unite
    );

    if ($stmt->execute()) {
        $success_message = "Le projet a été proposé avec succès!";
    } else {
        $error_message = "Erreur lors de la proposition du projet: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposer un Projet - Plateforme Projets Militaires</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
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
            padding: 20px;
        }
        
        .form-container {
            max-width: 900px;
            margin: 30px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .form-header h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .form-header p {
            color: #666;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .form-group label.required:after {
            content: " *";
            color: var(--accent-color);
        }
        
        .radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 5px;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            background: #f8f9fa;
            border-radius: 6px;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .radio-option:hover {
            background: #e9ecef;
        }
        
        .radio-option input {
            margin: 0;
        }
        
        .inline-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        textarea,
        select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        textarea:focus,
        select:focus {
            border-color: var(--secondary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .btn-submit {
            background-color: var(--success-color);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-submit:hover {
            background-color: #219653;
            transform: translateY(-2px);
        }
        
        .message {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .success {
            background-color: #d5f5e3;
            color: #27ae60;
            border-left: 4px solid var(--success-color);
        }
        
        .error {
            background-color: #fadbd8;
            color: #e74c3c;
            border-left: 4px solid var(--accent-color);
        }
        
        .custom-combobox {
            position: relative;
        }
        
        .custom-combobox::after {
            content: "▼";
            font-size: 12px;
            color: #777;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }
        
        .custom-combobox-select {
            appearance: none;
            padding-right: 30px;
        }
        
        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .character-count {
            font-size: 0.8rem;
            color: #777;
            text-align: right;
        }
        
        .text-danger {
            color: var(--accent-color);
        }
        
        @media (max-width: 768px) {
            .inline-group {
                grid-template-columns: 1fr;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h1><i class="fas fa-lightbulb"></i> Proposer un Projet</h1>
            <p>Remplissez ce formulaire pour soumettre un nouveau projet aux élèves</p>
        </div>
        
        <?php if ($success_message): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="projetForm">
            <!-- Titre du projet -->
            <div class="form-group">
                <label for="titre" class="required">Titre du projet</label>
                <input type="text" id="titre" name="titre" required placeholder="Donnez un titre clair et concis à votre projet">
            </div>

            <!-- Spécialité -->
            <div class="form-group">
                <label class="required">Spécialité</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="specialite" value="GC" required> 
                        <i class="fas fa-building"></i> Génie Civil
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="specialite" value="TEL"> 
                        <i class="fas fa-satellite-dish"></i> Télécommunication
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="specialite" value="EM"> 
                        <i class="fas fa-cogs"></i> Électromécanique
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="specialite" value="GI"> 
                        <i class="fas fa-laptop-code"></i> Génie Informatique
                    </label>
                </div>
            </div>

            <!-- Nombre d'élèves -->
            <div class="form-group">
                <label class="required">Nombre d'élèves</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="nombreEleves" value="Monome" required> 
                        <i class="fas fa-user"></i> Monôme
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="nombreEleves" value="Binome"> 
                        <i class="fas fa-user-friends"></i> Binôme
                    </label>
                </div>
            </div>

            <!-- Encadrant -->
            <div class="form-group">
                <label for="encadrant" class="required">Encadrant</label>
                <input type="text" id="encadrant" name="encadrant" required 
                       placeholder="Nom complet de l'encadrant" value="<?php echo htmlspecialchars($_SESSION['nom_unite'] ?? ''); ?>">
            </div>

            <!-- Organisme/Adresse -->
            <div class="form-group">
                <label for="organisme" class="required">Organisme/Adresse</label>
                <textarea id="organisme" name="organisme" required 
                          placeholder="Adresse complète de l'organisme où sera réalisé le projet"></textarea>
                <div class="character-count"><span id="organisme-count">0</span>/500 caractères</div>
            </div>

            <!-- Grade/Fonction et Téléphone/Email -->
            <div class="form-group inline-group">
                <div>
                    <label for="grade" class="required">Grade/Fonction</label>
                    <div class="custom-combobox">
                        <select id="grade" name="grade" class="custom-combobox-select" required>
                            <option value="">Sélectionnez votre grade</option>
                            <option value="Lieutenant">Lieutenant</option>
                            <option value="Capitaine">Capitaine</option>
                            <option value="Commandant">Commandant</option>
                            <option value="Lieutenant-Colonel">Lieutenant-Colonel</option>
                            <option value="Colonel">Colonel</option>
                            <option value="Colonel-major">Colonel-major</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="telephone" class="required">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" required 
                           placeholder="Numéro à 8 chiffres" pattern="[0-9]{8}">
                    <small class="text-danger" id="phone-error" style="display:none;">Le numéro doit contenir 8 chiffres</small>
                </div>
                <div>
                    <label for="email" class="required">Adresse Email</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="email@domaine.com" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>">
                    <small class="text-danger" id="email-error" style="display:none;">Adresse email invalide</small>
                </div>
            </div>

            <!-- Description du projet -->
            <div class="form-group">
                <label for="description" class="required">Description du projet</label>
                <textarea id="description" name="description" required 
                          placeholder="Décrivez en détail le projet à réaliser"></textarea>
                <div class="character-count"><span id="description-count">0</span>/2000 caractères</div>
            </div>

            <!-- Objectif -->
            <div class="form-group">
                <label for="objectif" class="required">Objectif</label>
                <textarea id="objectif" name="objectif" required 
                          placeholder="Quels sont les objectifs principaux de ce projet?"></textarea>
                <div class="character-count"><span id="objectif-count">0</span>/1000 caractères</div>
            </div>

            <!-- Résultats attendus -->
            <div class="form-group">
                <label for="resultats" class="required">Résultats attendus</label>
                <textarea id="resultats" name="resultats" required 
                          placeholder="Quels résultats concrets attendez-vous de ce projet?"></textarea>
                <div class="character-count"><span id="resultats-count">0</span>/1000 caractères</div>
            </div>

            <!-- Bouton de soumission -->
            <div class="form-footer">
                <div>
                    <small>Les champs marqués d'un * sont obligatoires</small>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Soumettre le projet
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form elements
            const form = document.getElementById('projetForm');
            const telephoneInput = document.getElementById('telephone');
            const emailInput = document.getElementById('email');
            const phoneError = document.getElementById('phone-error');
            const emailError = document.getElementById('email-error');
            
            // Character counters
            const textareas = ['organisme', 'description', 'objectif', 'resultats'];
            textareas.forEach(id => {
                const textarea = document.getElementById(id);
                const counter = document.getElementById(`${id}-count`);
                
                textarea.addEventListener('input', function() {
                    counter.textContent = this.value.length;
                    
                    // Highlight if approaching limit
                    const maxLength = parseInt(counter.parentNode.textContent.match(/\d+/)[0]);
                    if (this.value.length > maxLength * 0.9) {
                        counter.classList.add('text-danger');
                    } else {
                        counter.classList.remove('text-danger');
                    }
                });
                
                // Initialize counter
                counter.textContent = textarea.value.length;
            });

            // Phone number validation
            telephoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 8) {
                    value = value.slice(0, 8);
                }
                e.target.value = value;
                
                // Show error if incomplete
                if (value.length !== 8 && value.length > 0) {
                    phoneError.style.display = 'block';
                } else {
                    phoneError.style.display = 'none';
                }
            });

            // Email validation
            emailInput.addEventListener('input', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (!emailRegex.test(this.value) {
                    emailError.style.display = 'block';
                } else {
                    emailError.style.display = 'none';
                }
            });

            // Form submission validation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Validate phone
                if (telephoneInput.value.length !== 8) {
                    phoneError.style.display = 'block';
                    telephoneInput.focus();
                    isValid = false;
                }
                
                // Validate email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailInput.value)) {
                    emailError.style.display = 'block';
                    if (isValid) emailInput.focus();
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>