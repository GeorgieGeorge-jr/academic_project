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

// Function to get letter grade
function getLetterGrade($score) {
    if ($score >= 70) return 'A';
    if ($score >= 60) return 'B';
    if ($score >= 50) return 'C';
    if ($score >= 45) return 'D';
    if ($score >= 40) return 'E';
    return 'F';
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

if (!empty($errors)):
?>
    <div class="error-container">
        <div class="error-card">
            <div class="error-icon">❌</div>
            <h2>Validation Error</h2>
            <div class="error-list">
                <?php foreach ($errors as $error): ?>
                    <p class="error-item"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
            <a href="gpa.php" class="btn btn-primary">← Go Back</a>
        </div>
    </div>
<?php
else:
    // Calculate grade point for this course
    $grade_point = getGradePoint($score);
    $letter_grade = getLetterGrade($score);
    $total_points = $grade_point * $credit_unit;
    
    // Get student name
    $stmt = mysqli_prepare($conn, "SELECT name FROM students WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($result);
    
    // Save to database
    $insert_stmt = mysqli_prepare($conn,
        "INSERT INTO gpa_records (student_id, total_grade_points, total_credit_units, gpa) 
         VALUES (?, ?, ?, ?)"
    );
    
    $gpa = $total_points / $credit_unit; // GPA for this single course
    
    mysqli_stmt_bind_param($insert_stmt, "iddd", 
        $student_id, $total_points, $credit_unit, $gpa
    );
    
    if (mysqli_stmt_execute($insert_stmt)):
?>
    <div class="result-container">
        <!-- GPA Result Card -->
        <div class="result-card">
            <div class="result-header">
                <div class="result-icon">🎓</div>
                <h1>GPA Calculation Result</h1>
                <p class="student-name"><?php echo htmlspecialchars($student['name']); ?></p>
            </div>
            
            <div class="gpa-result-display">
                <div class="gpa-circle <?php 
                    if ($gpa >= 4.5) echo 'gpa-excellent';
                    elseif ($gpa >= 3.5) echo 'gpa-good';
                    elseif ($gpa >= 2.5) echo 'gpa-average';
                    elseif ($gpa >= 1.5) echo 'gpa-below-average';
                    else echo 'gpa-poor';
                ?>">
                    <span class="gpa-value"><?php echo number_format($gpa, 2); ?></span>
                    <span class="gpa-label">GPA</span>
                </div>
            </div>
            
            <!-- Course Details Table -->
            <div class="course-details">
                <h3>Course Details</h3>
                <table class="details-table">
                    <tr>
                        <td class="label">Course Name:</td>
                        <td class="value"><?php echo htmlspecialchars($course); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Score:</td>
                        <td class="value">
                            <span class="score-badge"><?php echo htmlspecialchars($score); ?>%</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Letter Grade:</td>
                        <td class="value">
                            <span class="grade-badge grade-<?php echo strtolower($letter_grade); ?>">
                                <?php echo $letter_grade; ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Grade Point:</td>
                        <td class="value"><?php echo number_format($grade_point, 1); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Credit Unit:</td>
                        <td class="value"><?php echo htmlspecialchars($credit_unit); ?></td>
                    </tr>
                    <tr class="total-row">
                        <td class="label">Total Points:</td>
                        <td class="value"><?php echo number_format($total_points, 2); ?></td>
                    </tr>
                </table>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="gpa.php" class="btn btn-secondary">
                    <span class="btn-icon">↩️</span>
                    Calculate Another
                </a>
                <a href="personal_details.php?student_id=<?php echo $student_id; ?>" class="btn btn-primary">
                    <span class="btn-icon">👤</span>
                    View Student Profile
                </a>
                <button onclick="window.print()" class="btn btn-outline">
                    <span class="btn-icon">🖨️</span>
                    Print Result
                </button>
            </div>
        </div>
        
        <!-- Grade Breakdown Card -->
        <div class="breakdown-card">
            <h3>📊 Grade Breakdown</h3>
            <div class="grade-meter">
                <div class="meter-label">
                    <span>Your Score: <?php echo $score; ?>%</span>
                    <span>Grade: <?php echo $letter_grade; ?> (<?php echo $grade_point; ?>)</span>
                </div>
                <div class="meter-bar">
                    <div class="meter-fill" style="width: <?php echo $score; ?>%;"></div>
                </div>
            </div>
            
            <div class="grade-scale-mini">
                <div class="scale-item scale-a">A (70-100)</div>
                <div class="scale-item scale-b">B (60-69)</div>
                <div class="scale-item scale-c">C (50-59)</div>
                <div class="scale-item scale-d">D (45-49)</div>
                <div class="scale-item scale-e">E (40-44)</div>
                <div class="scale-item scale-f">F (0-39)</div>
            </div>
        </div>
    </div>
<?php
    else:
?>
    <div class="error-container">
        <div class="error-card">
            <div class="error-icon">❌</div>
            <h2>Database Error</h2>
            <p class="error-message">Failed to save GPA record. Please try again.</p>
            <a href="gpa.php" class="btn btn-primary">← Try Again</a>
        </div>
    </div>
<?php
    endif;
endif;

include '../includes/footer.php';
?>