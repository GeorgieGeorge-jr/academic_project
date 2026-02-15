<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>


<?php
// Home page - Displays students and their registered courses
require_once 'config/db.php';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="home-page">
    <h1>Welcome to Student Management System</h1>
    <p class="subtitle">View all students and their registered courses</p>
    
    <div class="card-container">
        <?php
        // Fetch all students with their courses using JOIN
        $query = "SELECT s.id, s.matric_number, s.name, 
                         c.course_code, c.course_title
                  FROM students s
                  LEFT JOIN student_courses sc ON s.id = sc.student_id
                  LEFT JOIN courses c ON sc.course_id = c.id
                  ORDER BY s.name, c.course_code";
        
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $current_student = null;
            $student_courses = [];
            
            // Group courses by student
            while ($row = mysqli_fetch_assoc($result)) {
                $student_id = $row['id'];
                
                if ($current_student != $student_id) {
                    // Display previous student's card if exists
                    if ($current_student !== null) {
                        displayStudentCard($current_student_data, $student_courses);
                    }
                    
                    // Start new student
                    $current_student = $student_id;
                    $current_student_data = $row;
                    $student_courses = [];
                }
                
                // Add course if exists
                if ($row['course_code']) {
                    $student_courses[] = [
                        'code' => $row['course_code'],
                        'title' => $row['course_title']
                    ];
                }
            }
            
            // Display last student
            if ($current_student !== null) {
                displayStudentCard($current_student_data, $student_courses);
            }
        } else {
            echo "<p>No students found.</p>";
        }
        
        // Helper function to display student card
        function displayStudentCard($student, $courses) {
            ?>
            <div class="card">
                <div class="card-header">
                    <?php echo htmlspecialchars($student['name']); ?>
                </div>
                <div class="card-body">
                    <p><strong>Matric Number:</strong> <?php echo htmlspecialchars($student['matric_number']); ?></p>
                    <p><strong>Registered Courses:</strong></p>
                    <?php if (empty($courses)): ?>
                        <p>No courses registered</p>
                    <?php else: ?>
                        <ul style="margin-left: 20px;">
                            <?php foreach ($courses as $course): ?>
                                <li><?php echo htmlspecialchars($course['code'] . ' - ' . $course['title']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<?php
include 'includes/footer.php';
?>