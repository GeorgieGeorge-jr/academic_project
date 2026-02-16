<?php
require_once 'config/db.php';

echo "<h2>Debugging Student-Course Relationships</h2>";

// Count total registrations
$count_query = "SELECT COUNT(*) as total FROM student_courses";
$count_result = mysqli_query($conn, $count_query);
$count = mysqli_fetch_assoc($count_result);
echo "<p><strong>Total registrations in student_courses:</strong> " . $count['total'] . "</p>";
echo "<p><em>(Should be " . (8 * 8) . " if 8 students take all 8 courses)</em></p>";

// Show all students with their courses
echo "<h3>All Students and Their Courses:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Student ID</th><th>Student Name</th><th>Course Code</th><th>Course Title</th></tr>";

$query = "SELECT s.id, s.name, c.course_code, c.course_title
          FROM students s
          LEFT JOIN student_courses sc ON s.id = sc.student_id
          LEFT JOIN courses c ON sc.course_id = c.id
          ORDER BY s.id, c.course_code";

$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . ($row['course_code'] ?? 'NO COURSES') . "</td>";
    echo "<td>" . ($row['course_title'] ?? '') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check for students with no courses
echo "<h3>Students with NO courses:</h3>";
$no_courses_query = "SELECT s.id, s.name 
                     FROM students s
                     LEFT JOIN student_courses sc ON s.id = sc.student_id
                     WHERE sc.id IS NULL";
$no_courses_result = mysqli_query($conn, $no_courses_query);

if (mysqli_num_rows($no_courses_result) > 0) {
    while ($row = mysqli_fetch_assoc($no_courses_result)) {
        echo "<p>⚠️ " . htmlspecialchars($row['name']) . " (ID: " . $row['id'] . ") has no courses!</p>";
    }
} else {
    echo "<p>✅ All students have courses!</p>";
}

// Show total courses per student
echo "<h3>Course Count Per Student:</h3>";
$count_per_student = "SELECT s.id, s.name, COUNT(sc.course_id) as course_count
                      FROM students s
                      LEFT JOIN student_courses sc ON s.id = sc.student_id
                      GROUP BY s.id, s.name
                      ORDER BY s.id";

$count_result = mysqli_query($conn, $count_per_student);
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Student</th><th>Number of Courses</th><th>Expected</th></tr>";

while ($row = mysqli_fetch_assoc($count_result)) {
    $expected = 8; // Total number of courses
    $status = ($row['course_count'] == $expected) ? '✅' : '❌';
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . $row['course_count'] . " " . $status . "</td>";
    echo "<td>" . $expected . "</td>";
    echo "</tr>";
}
echo "</table>";