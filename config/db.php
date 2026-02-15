<?php
// Database configuration file
// This file handles the connection to MySQL database

// Database credentials
$host = 'localhost';      // Server name (default for XAMPP/MAMP)
$username = 'root';       // Default username for XAMPP/MAMP
$password = '';           // Default password (empty for XAMPP, 'root' for MAMP)
$database = 'academic_project';

// Create connection with mysqli
$conn = mysqli_connect($host, $username, $password, $database);

// Check if connection was successful
if (!$conn) {
    // If connection fails, stop execution and show error
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8 for proper character handling
mysqli_set_charset($conn, "utf8");
?>