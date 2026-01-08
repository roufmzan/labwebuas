<?php
require_once 'config/database.php';

// User data
$username = 'admin';
$password = 'admin123';
$role = 'admin'; // You can change this to 'user' if needed

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Prepare an insert statement
$sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("sss", $username, $hashed_password, $role);
    
    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        echo "User created successfully. Username: " . htmlspecialchars($username);
    } else {
        echo "Error: " . $stmt->error;
    }
    
    // Close statement
    $stmt->close();
} else {
    echo "Error preparing statement: " . $conn->error;
}

// Close connection
$conn->close();
?>
