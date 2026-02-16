<?php
// Process GPA calculation with assessment components
require_once '../config/db.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: gpa.php');
    exit();
}

// Function to convert total score to grade and grade point
function calculateGrade($total_score) {
    if ($total_score >= 70) return ['A', 5.0];
    if ($total_score >= 60) return ['B', 4.0];
    if ($total_score >= 50) return ['C', 3.0];
    if ($total_score >= 45) return ['D', 2.0];
    if ($total_score >= 40) return ['E', 1.0];
    return ['F', 0.0];
}

// Get and validate form data
$student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
$course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);

// Get assessment scores (allow empty values)
$quiz_1 = filter_input(INPUT_POST, 'quiz_1', FILTER_VALIDATE_FLOAT) ?: 0;
$quiz_2 = filter_input(INPUT_POST, 'quiz_2', FILTER_VALIDATE_FLOAT) ?: 0;
$quiz_3 = filter_input(INPUT_POST, 'quiz_3', FILTER_VALIDATE_FLOAT) ?: 0;
$mid_semester = filter_input(INPUT_POST, 'mid_semester', FILTER_VALIDATE_FLOAT) ?: 0;
$project = filter_input(INPUT_POST, 'project', FILTER_VALIDATE_FLOAT) ?: 0;
$attendance = filter_input(INPUT_POST, 'attendance', FILTER_VALIDATE_FLOAT) ?: 0;
$exam = filter_input(INPUT_POST, 'exam', FILTER_VALIDATE_FLOAT) ?: 0;

// Validate required fields
$errors = [];
if (!$student_id) $errors[] = "Invalid student selected.";
if (!$course_id) $errors[] = "Please select a course.";

// Validate score ranges
if ($quiz_1 < 0 || $quiz_1 > 10) $errors[] = "Quiz 1 score must be between 0 and 10.";
if ($quiz_2 < 0 || $quiz_2 > 10) $errors[] = "Quiz 2 score must be between 0 and 10.";
if ($quiz_3 < 0 || $quiz_3 > 10) $errors[] = "Quiz 3 score must be between 0 and 10.";
if ($mid_semester < 0 || $mid_semester > 15) $errors[] = "Mid semester score must be between 0 and 15.";
if ($project < 0 || $project > 20) $errors[] = "Project score must be between 0 and 20.";
if ($attendance < 0 || $attendance > 5) $errors[] = "Attendance score must be between 0 and 5.";
if ($exam < 0 || $exam > 50) $errors[] = "Exam score must be between 0 and 50.";

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
            <a href="gpa.php?student_id=<?php echo $student_id; ?>" class="btn btn-primary">← Go Back</a>
        </div>
    </div>
<?php
else:
    // Calculate total score
    $total_score = $quiz_1 + $quiz_2 + $quiz_3 + $mid_semester + $project + $attendance + $exam;
    
    // Get grade and grade point
    list($grade, $grade_point) = calculateGrade($total_score);
    
    // Get course credit unit
    $credit_stmt = mysqli_prepare($conn, "SELECT credit_unit FROM courses WHERE id = ?");
    mysqli_stmt_bind_param($credit_stmt, "i", $course_id);
    mysqli_stmt_execute($credit_stmt);
    $credit_result = mysqli_stmt_get_result($credit_stmt);
    $course = mysqli_fetch_assoc($credit_result);
    $credit_unit = $course['credit_unit'];
    
    // Get student name
    $student_stmt = mysqli_prepare($conn, "SELECT name FROM students WHERE id = ?");
    mysqli_stmt_bind_param($student_stmt, "i", $student_id);
    mysqli_stmt_execute($student_stmt);
    $student_result = mysqli_stmt_get_result($student_stmt);
    $student = mysqli_fetch_assoc($student_result);
    
    // Check if record exists
    $check_stmt = mysqli_prepare($conn, "SELECT id FROM course_assessments WHERE student_id = ? AND course_id = ?");
    mysqli_stmt_bind_param($check_stmt, "ii", $student_id, $course_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Update existing record
        $update_stmt = mysqli_prepare($conn,
            "UPDATE course_assessments SET 
                quiz_1 = ?, quiz_2 = ?, quiz_3 = ?,
                mid_semester = ?, project = ?, attendance = ?, exam = ?,
                total_score = ?, grade = ?, grade_point = ?
             WHERE student_id = ? AND course_id = ?"
        );
        mysqli_stmt_bind_param($update_stmt, "ddddddddssii", 
            $quiz_1, $quiz_2, $quiz_3, $mid_semester, $project, $attendance, $exam,
            $total_score, $grade, $grade_point, $student_id, $course_id
        );
        $success = mysqli_stmt_execute($update_stmt);
        $action = "updated";
    } else {
        // Insert new record
        $insert_stmt = mysqli_prepare($conn,
            "INSERT INTO course_assessments 
                (student_id, course_id, quiz_1, quiz_2, quiz_3, mid_semester, project, attendance, exam, total_score, grade, grade_point)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($insert_stmt, "iiddddddddsd", 
            $student_id, $course_id, $quiz_1, $quiz_2, $quiz_3, $mid_semester, $project, $attendance, $exam,
            $total_score, $grade, $grade_point
        );
        $success = mysqli_stmt_execute($insert_stmt);
        $action = "saved";
    }
    
    if ($success):
?>
    <div class="result-container">
        <!-- Success Message -->
        <div class="success-card">
            <div class="success-icon">✅</div>
            <h2>Assessment <?php echo $action; ?> Successfully!</h2>
            <p class="student-name"><?php echo htmlspecialchars($student['name']); ?></p>
        </div>

        <!-- Results Summary -->
        <div class="results-grid">
            <!-- Score Breakdown Card -->
            <div class="result-card score-card">
                <h3>📊 Score Breakdown</h3>
                <div class="score-list">
                    <div class="score-item">
                        <span class="score-label">Quizzes (10%):</span>
                        <span class="score-value"><?php echo number_format($quiz_1 + $quiz_2 + $quiz_3, 1); ?>/30</span>
                    </div>
                    <div class="score-item">
                        <span class="score-label">Mid Semester (15%):</span>
                        <span class="score-value"><?php echo number_format($mid_semester, 1); ?>/15</span>
                    </div>
                    <div class="score-item">
                        <span class="score-label">Project (20%):</span>
                        <span class="score-value"><?php echo number_format($project, 1); ?>/20</span>
                    </div>
                    <div class="score-item">
                        <span class="score-label">Attendance (5%):</span>
                        <span class="score-value"><?php echo number_format($attendance, 1); ?>/5</span>
                    </div>
                    <div class="score-item">
                        <span class="score-label">Exam (50%):</span>
                        <span class="score-value"><?php echo number_format($exam, 1); ?>/50</span>
                    </div>
                    <div class="score-item total">
                        <span class="score-label">Total Score:</span>
                        <span class="score-value <?php echo $total_score >= 70 ? 'text-success' : ($total_score >= 50 ? 'text-warning' : 'text-danger'); ?>">
                            <strong><?php echo number_format($total_score, 1); ?>/100</strong>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Grade Card -->
            <div class="result-card grade-card">
                <h3>🎓 Grade Information</h3>
                <div class="grade-display">
                    <div class="grade-circle grade-<?php echo strtolower($grade); ?>">
                        <span class="grade-letter"><?php echo $grade; ?></span>
                    </div>
                    <div class="grade-details">
                        <div class="grade-point-display">
                            <span class="label">Grade Point:</span>
                            <span class="value"><?php echo number_format($grade_point, 1); ?></span>
                        </div>
                        <div class="credit-display">
                            <span class="label">Credit Unit:</span>
                            <span class="value"><?php echo $credit_unit; ?></span>
                        </div>
                        <div class="quality-points">
                            <span class="label">Quality Points:</span>
                            <span class="value"><?php echo number_format($grade_point * $credit_unit, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Meter -->
            <div class="result-card meter-card">
                <h3>📈 Performance Meter</h3>
                <div class="meter-container">
                    <div class="meter-scale">
                        <div class="meter-fill" style="width: <?php echo $total_score; ?>%;"></div>
                    </div>
                    <div class="meter-labels">
                        <span>0%</span>
                        <span class="meter-value"><?php echo number_format($total_score, 1); ?>%</span>
                        <span>100%</span>
                    </div>
                </div>
                <div class="grade-scale-reference">
                    <div class="scale-item scale-a">A (70-100)</div>
                    <div class="scale-item scale-b">B (60-69)</div>
                    <div class="scale-item scale-c">C (50-59)</div>
                    <div class="scale-item scale-d">D (45-49)</div>
                    <div class="scale-item scale-e">E (40-44)</div>
                    <div class="scale-item scale-f">F (0-39)</div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="gpa.php?student_id=<?php echo $student_id; ?>" class="btn btn-primary">
                <span class="btn-icon">📋</span>
                Back to GPA Dashboard
            </a>
            <a href="personal_details.php?student_id=<?php echo $student_id; ?>" class="btn btn-secondary">
                <span class="btn-icon">👤</span>
                View Student Profile
            </a>
            <button onclick="window.print()" class="btn btn-outline">
                <span class="btn-icon">🖨️</span>
                Print Results
            </button>
        </div>
    </div>

<?php
    else:
?>
    <div class="error-container">
        <div class="error-card">
            <div class="error-icon">❌</div>
            <h2>Database Error</h2>
            <p class="error-message">Failed to save assessment. Please try again.</p>
            <p class="error-details"><?php echo mysqli_error($conn); ?></p>
            <a href="gpa.php?student_id=<?php echo $student_id; ?>" class="btn btn-primary">← Try Again</a>
        </div>
    </div>
<?php
    endif;
endif;

include '../includes/footer.php';
?>