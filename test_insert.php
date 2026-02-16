<?php
require_once 'config/db.php';

echo "<h2>Testing Database Insert</h2>";

// Check current state
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM students");
$row = mysqli_fetch_assoc($result);
echo "<p>Current students count: " . $row['count'] . "</p>";

// Try to insert one test student
$test_matric = 'TEST001';
$test_name = 'Test Student';

$insert = "INSERT INTO students (matric_number, name) VALUES ('$test_matric', '$test_name')";

if (mysqli_query($conn, $insert)) {
    echo "<p style='color:green'>✅ Test insert successful!</p>";
} else {
    echo "<p style='color:red'>❌ Test insert failed: " . mysqli_error($conn) . "</p>";
}

// Show final count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM students");
$row = mysqli_fetch_assoc($result);
echo "<p>Final students count: " . $row['count'] . "</p>";
?>