<?php
// GPA page - Enter course scores to calculate GPA
require_once '../config/db.php';
include '../includes/header.php';
include '../includes/navbar.php';

// Fetch all students for dropdown
$students_query = "SELECT id, name, matric_number FROM students ORDER BY name";
$students_result = mysqli_query($conn, $students_query);

// Get selected student if any
$selected_student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$selected_student = null;
$student_courses = [];

if ($selected_student_id > 0) {
    // Get student details
    $student_stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
    mysqli_stmt_bind_param($student_stmt, "i", $selected_student_id);
    mysqli_stmt_execute($student_stmt);
    $student_result = mysqli_stmt_get_result($student_stmt);
    $selected_student = mysqli_fetch_assoc($student_result);
    
    // Get all courses with their assessment data for this student
    $courses_stmt = mysqli_prepare($conn, 
        "SELECT c.*, 
                ca.quiz_1, ca.quiz_2, ca.quiz_3, 
                ca.mid_semester, ca.project, ca.attendance, ca.exam,
                ca.total_score, ca.grade, ca.grade_point
         FROM courses c
         LEFT JOIN course_assessments ca ON c.id = ca.course_id AND ca.student_id = ?
         ORDER BY c.course_code"
    );
    mysqli_stmt_bind_param($courses_stmt, "i", $selected_student_id);
    mysqli_stmt_execute($courses_stmt);
    $student_courses = mysqli_stmt_get_result($courses_stmt);
}
?>

<div class="gpa-page">
    <!-- Page Header -->
    <div class="page-header">
        <h1><span class="header-icon">📊</span> GPA Calculator</h1>
        <p class="header-subtitle">Calculate and track student performance across all courses</p>
    </div>

    <!-- Student Selection Card -->
    <div class="selection-card">
        <form method="GET" action="" class="student-selector">
            <div class="form-group">
                <label for="student_id">
                    <span class="label-icon">👤</span>
                    Select Student:
                </label>
                <div class="select-wrapper">
                    <select name="student_id" id="student_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Choose a student --</option>
                        <?php while ($student = mysqli_fetch_assoc($students_result)): ?>
                            <option value="<?php echo $student['id']; ?>" 
                                <?php echo $student['id'] == $selected_student_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($student['name'] . ' (' . $student['matric_number'] . ')'); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <span class="select-arrow">▼</span>
                </div>
            </div>
        </form>
    </div>

    <?php if ($selected_student): ?>
        <!-- Student Info Card -->
        <div class="student-info-card">
            <div class="student-avatar">
                <span class="avatar-initials">
                    <?php 
                    $name_parts = explode(' ', $selected_student['name']);
                    $initials = '';
                    foreach ($name_parts as $part) {
                        $initials .= strtoupper(substr($part, 0, 1));
                    }
                    echo $initials;
                    ?>
                </span>
            </div>
            <div class="student-details">
                <h2><?php echo htmlspecialchars($selected_student['name']); ?></h2>
                <div class="student-meta">
                    <span class="meta-item">
                        <span class="meta-icon">🎓</span>
                        Matric: <?php echo htmlspecialchars($selected_student['matric_number']); ?>
                    </span>
                    <span class="meta-item">
                        <span class="meta-icon">🩸</span>
                        Blood Group: <?php echo htmlspecialchars($selected_student['blood_group'] ?: 'N/A'); ?>
                    </span>
                    <span class="meta-item">
                        <span class="meta-icon">📍</span>
                        State: <?php echo htmlspecialchars($selected_student['state_of_origin'] ?: 'N/A'); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Assessment Form for Selected Course -->
        <div class="assessment-section">
            <div class="section-header">
                <h3><span class="section-icon">📝</span> Course Assessment Entry</h3>
                <p>Select a course to enter scores (Maximum scores in brackets)</p>
            </div>

            <form action="gpa_process.php" method="POST" class="assessment-form" id="assessmentForm">
                <input type="hidden" name="student_id" value="<?php echo $selected_student_id; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="course_id">Select Course:</label>
                        <select name="course_id" id="course_id" class="form-control" required>
                            <option value="">-- Choose a course --</option>
                            <?php 
                            mysqli_data_seek($student_courses, 0); // Reset pointer
                            while ($course = mysqli_fetch_assoc($student_courses)): 
                            ?>
                                <option value="<?php echo $course['id']; ?>">
                                    <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_title'] . ' (' . $course['credit_unit'] . ' credits)'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <!-- Assessment Components Grid -->
                <div class="assessment-grid">
                    <div class="assessment-category">
                        <h4>Quizzes (Max 10%)</h4>
                        <div class="score-inputs">
                            <div class="input-group">
                                <label for="quiz_1">Quiz 1:</label>
                                <input type="number" name="quiz_1" id="quiz_1" class="score-input" 
                                       min="0" max="10" step="0.5" placeholder="0-10">
                            </div>
                            <div class="input-group">
                                <label for="quiz_2">Quiz 2:</label>
                                <input type="number" name="quiz_2" id="quiz_2" class="score-input" 
                                       min="0" max="10" step="0.5" placeholder="0-10">
                            </div>
                            <div class="input-group">
                                <label for="quiz_3">Quiz 3:</label>
                                <input type="number" name="quiz_3" id="quiz_3" class="score-input" 
                                       min="0" max="10" step="0.5" placeholder="0-10">
                            </div>
                        </div>
                    </div>

                    <div class="assessment-category">
                        <h4>Mid Semester (Max 15%)</h4>
                        <div class="input-group">
                            <label for="mid_semester">Mid Semester Score:</label>
                            <input type="number" name="mid_semester" id="mid_semester" class="score-input" 
                                   min="0" max="15" step="0.5" placeholder="0-15">
                        </div>
                    </div>

                    <div class="assessment-category">
                        <h4>Project (Max 20%)</h4>
                        <div class="input-group">
                            <label for="project">Project Score:</label>
                            <input type="number" name="project" id="project" class="score-input" 
                                   min="0" max="20" step="0.5" placeholder="0-20">
                        </div>
                    </div>

                    <div class="assessment-category">
                        <h4>Attendance (Max 5%)</h4>
                        <div class="input-group">
                            <label for="attendance">Attendance Score:</label>
                            <input type="number" name="attendance" id="attendance" class="score-input" 
                                   min="0" max="5" step="0.5" placeholder="0-5">
                        </div>
                    </div>

                    <div class="assessment-category">
                        <h4>Exam (Max 50%)</h4>
                        <div class="input-group">
                            <label for="exam">Exam Score:</label>
                            <input type="number" name="exam" id="exam" class="score-input" 
                                   min="0" max="50" step="0.5" placeholder="0-50">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-icon">💾</span>
                        Save Assessment & Calculate GPA
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <span class="btn-icon">🔄</span>
                        Reset Form
                    </button>
                </div>
            </form>
        </div>

        <!-- Courses Table with Existing Scores -->
        <div class="courses-section">
            <div class="section-header">
                <h3><span class="section-icon">📚</span> Course Performance Summary</h3>
                <p>All courses with their current assessment scores</p>
            </div>

            <div class="table-responsive">
                <table class="courses-table">
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Title</th>
                            <th>Credit</th>
                            <th>Quiz (10)</th>
                            <th>Mid Sem (15)</th>
                            <th>Project (20)</th>
                            <th>Attend (5)</th>
                            <th>Exam (50)</th>
                            <th>Total</th>
                            <th>Grade</th>
                            <th>GP</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($student_courses, 0);
                        $total_grade_points = 0;
                        $total_credits = 0;
                        
                        while ($course = mysqli_fetch_assoc($student_courses)): 
                            $total = $course['total_score'] ?? 0;
                            $grade_point = $course['grade_point'] ?? 0;
                            $credit = $course['credit_unit'];
                            
                            if ($grade_point > 0) {
                                $total_grade_points += $grade_point * $credit;
                                $total_credits += $credit;
                            }
                        ?>
                            <tr>
                                <td class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td class="course-title"><?php echo htmlspecialchars($course['course_title']); ?></td>
                                <td class="credit"><?php echo $credit; ?></td>
                                <td><?php echo $course['quiz_1'] ?? '-'; ?>/<?php echo $course['quiz_2'] ?? '-'; ?>/<?php echo $course['quiz_3'] ?? '-'; ?></td>
                                <td><?php echo $course['mid_semester'] ?? '-'; ?></td>
                                <td><?php echo $course['project'] ?? '-'; ?></td>
                                <td><?php echo $course['attendance'] ?? '-'; ?></td>
                                <td><?php echo $course['exam'] ?? '-'; ?></td>
                                <td class="total-score <?php echo $total >= 70 ? 'text-success' : ($total >= 50 ? 'text-warning' : 'text-danger'); ?>">
                                    <strong><?php echo $total ? number_format($total, 1) : '-'; ?></strong>
                                </td>
                                <td>
                                    <?php if ($course['grade']): ?>
                                        <span class="grade-badge grade-<?php echo strtolower($course['grade']); ?>">
                                            <?php echo $course['grade']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="grade-badge grade-pending">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="grade-point"><?php echo $grade_point ? number_format($grade_point, 1) : '-'; ?></td>
                                <td class="actions">
                                    <button class="btn-icon-small edit-btn" title="Edit" 
                                            onclick="editCourse(<?php echo htmlspecialchars(json_encode($course)); ?>)">
                                        ✏️
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr class="summary-row">
                            <td colspan="2" class="summary-label">Cumulative GPA:</td>
                            <td class="summary-credit"><?php echo $total_credits; ?> Credits</td>
                            <td colspan="7"></td>
                            <td colspan="2" class="summary-gpa">
                                <?php 
                                if ($total_credits > 0) {
                                    $cgpa = $total_grade_points / $total_credits;
                                    $cgpa_class = $cgpa >= 4.5 ? 'gpa-excellent' : ($cgpa >= 3.5 ? 'gpa-good' : ($cgpa >= 2.5 ? 'gpa-average' : ($cgpa >= 1.5 ? 'gpa-below-average' : 'gpa-poor')));
                                ?>
                                    <span class="gpa-badge <?php echo $cgpa_class; ?>">
                                        <?php echo number_format($cgpa, 2); ?>
                                    </span>
                                <?php } else { ?>
                                    <span class="gpa-badge">No GPA yet</span>
                                <?php } ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    <?php elseif ($selected_student_id > 0): ?>
        <div class="error-message">
            <p>Student not found. Please select a valid student.</p>
        </div>
    <?php else: ?>
        <div class="welcome-message">
            <div class="welcome-icon">📊</div>
            <h2>Welcome to GPA Calculator</h2>
            <p>Please select a student from the dropdown above to begin entering scores and calculating GPA.</p>
        </div>
    <?php endif; ?>
</div>

<script>
// Simple JavaScript to populate edit form (no frameworks)
function editCourse(courseData) {
    document.getElementById('course_id').value = courseData.id;
    document.getElementById('quiz_1').value = courseData.quiz_1 || '';
    document.getElementById('quiz_2').value = courseData.quiz_2 || '';
    document.getElementById('quiz_3').value = courseData.quiz_3 || '';
    document.getElementById('mid_semester').value = courseData.mid_semester || '';
    document.getElementById('project').value = courseData.project || '';
    document.getElementById('attendance').value = courseData.attendance || '';
    document.getElementById('exam').value = courseData.exam || '';
    
    // Scroll to form
    document.getElementById('assessmentForm').scrollIntoView({ behavior: 'smooth' });
}
</script>

<?php
include '../includes/footer.php';
?>