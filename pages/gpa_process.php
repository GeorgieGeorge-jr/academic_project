<?php
// Process GPA calculation
require_once '../config/db.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: gpa.php');
    exit();
}

// Function to convert score to grade point
function getGradePoint($score) {
    if ($score >= 70 && $score <= 100) return 5.0;  // A
    if ($score >= 60 && $score <= 69) return 4.0;   // B
    if ($score >= 50 && $score <= 59) return 3.0;   // C
    if ($score >= 45 && $score <= 49) return 2.0;   // D
    if ($score >= 40 && $score <= 44) return 1.0;   // E
    return 0.0;  // F
}

// Get and validate form data
$student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
$course = trim($_POST['course'] ?? '');
$score = filter_input(INPUT_POST, 'score', FILTER_VALIDATE_FLOAT);
$credit_unit = filter_input(INPUT_POST, 'credit_unit', FILTER_VALIDATE_INT);

// Validate inputs
$errors = [];
if (!$student_id) $errors[] = "Please select a valid student.";
if (empty($course)) $errors[] = "Please enter course name.";
if ($score === false || $score < 0 || $score > 100) $errors[] = "Please enter a valid score between 0 and 100.";
if (!$credit_unit || $credit_unit < 1 || $credit_unit > 5) $errors[] = "Please enter valid credit units (1-5).";

include '../includes/header.php';
include '../includes/navbar.php';

if (!empty($errors)) {
    // Display errors
    echo '<div class="form-container">';
    echo '<div class="alert alert-error">';
    foreach ($errors as $error) {
        echo '<p>' . htmlspecialchars($error) . '</p>';
    }
    echo '</div>';
    echo '<a href="gpa.php" class="btn">Go Back</a>';
    echo '</div>';
} else {
    // Calculate grade point for this course
    $grade_point = getGradePoint($score);
    $total_points = $grade_point * $credit_unit;
    
    // Get student name
    $stmt = mysqli_prepare($conn, "SELECT name FROM students WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($result);
    
    // For simplicity, we'll create a new GPA record for each calculation
    // In a real system, you might want to accumulate multiple courses
    
    // Save to database
    $insert_stmt = mysqli_prepare($conn,
        "INSERT INTO gpa_records (student_id, total_grade_points, total_credit_units, gpa) 
         VALUES (?, ?, ?, ?)"
    );
    
    $gpa = $total_points / $credit_unit; // GPA for this single course
    
    mysqli_stmt_bind_param($insert_stmt, "iddd", 
        $student_id, $total_points, $credit_unit, $gpa
    );
    mysqli_stmt_execute($insert_stmt);
    
    // Display result
    ?>
    <div class="payslip"> <!-- Reusing payslip style for consistency -->
        <div class="payslip-header">
            <h2>GPA Calculation Result</h2>
            <h3><?php echo htmlspecialchars($student['name']); ?></h3>
        </div>
        
        <div class="payslip-details">
            <div class="payslip-row">
                <span class="payslip-label">Course:</span>
                <span class="payslip-value"><?php echo htmlspecialchars($course); ?></span>
            </div>
            <div class="payslip-row">
                <span class="payslip-label">Score:</span>
                <span class="payslip-value"><?php echo htmlspecialchars($score); ?>%</span>
            </div>
            <div class="payslip-row">
                <span class="payslip-label">Grade Point:</span>
                <span class="payslip-value"><?php echo number_format($grade_point, 1); ?></span>
            </div>
            <div class="payslip-row">
                <span class="payslip-label">Credit Unit:</span>
                <span class="payslip-value"><?php echo htmlspecialchars($credit_unit); ?></span>
            </div>
            <div class="payslip-row">
                <span class="payslip-label">Total Points:</span>
                <span class="payslip-value"><?php echo number_format($total_points, 2); ?></span>
            </div>
            <div class="payslip-total">
                <div class="payslip-row">
                    <span class="payslip-label">GPA:</span>
                    <span class="payslip-value"><strong><?php echo number_format($gpa, 2); ?></strong></span>
                </div>
            </div>
        </div>
        
        <!-- Grade Scale Reference -->
        <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
            <h4>Grade Scale:</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 0.5rem;">
                <div>70-100: A (5.0)</div>
                <div>60-69: B (4.0)</div>
                <div>50-59: C (3.0)</div>
                <div>45-49: D (2.0)</div>
                <div>40-44: E (1.0)</div>
                <div>Below 40: F (0)</div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <a href="gpa.php" class="btn">Calculate Another</a>
        </div>
    </div>
    <?php
}

include '../includes/footer.php';
?>