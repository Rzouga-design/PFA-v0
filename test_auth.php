<?php
require_once 'config.php';

function testLogin($nom_unite, $role, $password) {
    global $conn;
    
    $hashed_password = hash('sha256', $password);
    
    $sql = "SELECT * FROM unites WHERE nom_unite = ? AND role = ? AND mot_de_passe = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nom_unite, $role, $hashed_password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        echo "✅ Login successful for $nom_unite as $role\n";
        return true;
    } else {
        echo "❌ Login failed for $nom_unite as $role\n";
        return false;
    }
}

echo "Testing Authentication System\n";
echo "===========================\n\n";

// Test Académie militaire credentials
echo "Testing Académie militaire credentials:\n";
testLogin('Académie militaire', 'admin', 'admin123');
testLogin('Académie militaire', 'encadrant', 'encadrant123');
testLogin('Académie militaire', 'eleve', 'eleve123');

echo "\nTesting other units (encadrant role):\n";
testLogin('Hôpital militaire principal d\'instruction de Tunis', 'encadrant', 'hopital123');
testLogin('D.G.T.I', 'encadrant', 'dgti123');
testLogin('D.G.M.A', 'encadrant', 'dgma123');
testLogin('Régiment 71 transmission', 'encadrant', 'regiment123');
testLogin('D.G.M.R.E', 'encadrant', 'dgmre123');

// Test invalid credentials
echo "\nTesting invalid credentials:\n";
testLogin('Académie militaire', 'admin', 'wrongpassword');
testLogin('Invalid Unit', 'encadrant', 'anypassword');
?> 