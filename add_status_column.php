<?php
require_once 'config.php';

// Add status column to projets table
$sql = "ALTER TABLE projets ADD COLUMN statut VARCHAR(20) DEFAULT 'disponible'";

if ($conn->query($sql)) {
    echo "Column 'statut' added successfully to projets table";
} else {
    echo "Error adding column: " . $conn->error;
}

// Close connection
$conn->close();
?> 