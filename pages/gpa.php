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
$student_courses = null;

if ($selected_student_id > 0) {
    // 1. Get student details
    $student_stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
    mysqli_stmt_bind_param($student_stmt, "i", $selected_student_id);
    mysqli_stmt_execute($student_stmt);
    $student_result = mysqli_stmt_get_result($student_stmt);
    $selected_student = mysqli_fetch_assoc($student_result);
    
    // 2. Get all courses with assessment data
    // Note: We alias c.id to course_ref_id to avoid conflict with course_assessments.id
    $courses_stmt = mysqli_prepare($conn, 
        "SELECT c.id AS course_ref_id, c.course_code, c.course_title, c.credit_unit,
                ca.id AS assessment_id, ca.quiz_1, ca.quiz_2, ca.quiz_3, 
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
    <div class="page-header">
        <h1><span class="header-icon">📊</span> GPA Calculator</h1>
        <p class="header-subtitle">Calculate and track student performance across all courses</p>
    </div>

    <div class="selection-card">
        <form method="GET" action="" class="student-selector">
            <div class="form-group">
                <label for="student_id">
                    <span class="label-icon">👤</span> Select Student:
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
                        <span class="meta-icon">📍</span>
                        State: <?php echo htmlspecialchars($selected_student['state_of_origin'] ?? 'N/A'); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="assessment-section">
            <div class="section-header">
                <h3><span class="section-icon">📝</span> Course Assessment Entry</h3>
                <p>Select a course to enter scores</p>
            </div>

            <form action="gpa_process.php" method="POST" class="assessment-form" id="assessmentForm">
                <input type="hidden" name="student_id" value="<?php echo $selected_student_id; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="course_id">Select Course:</label>
                        <select name="course_id" id="course_id" class="form-control" required>
                            <option value="">-- Choose a course --</option>
                            <?php 
                            if ($student_courses && mysqli_num_rows($student_courses) > 0) {
                                mysqli_data_seek($student_courses, 0); // Reset pointer for first loop
                                while ($course = mysqli_fetch_assoc($student_courses)): 
                            ?>
                                <option value="<?php echo $course['course_ref_id']; ?>">
                                    <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_title']); ?>
                                </option>
                            <?php
                                endwhile;
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="assessment-grid">
                    <div class="assessment-category">
                        <h4>Quizzes (Max 10%)</h4>
                        <div class="score-inputs">
                            <div class="input-group">
                                <label for="quiz_1">Quiz 1:</label>
                                <input type="number" name="quiz_1" id="quiz_1" class="score-input" min="0" max="10" step="0.5">
                            </div>
                            <div class="input-group">
                                <label for="quiz_2">Quiz 2:</label>
                                <input type="number" name="quiz_2" id="quiz_2" class="score-input" min="0" max="10" step="0.5">
                            </div>
                            <div class="input-group">
                                <label for="quiz_3">Quiz 3:</label>
                                <input type="number" name="quiz_3" id="quiz_3" class="score-input" min="0" max="10" step="0.5">
                            </div>
                        </div>
                    </div>

                    <div class="assessment-category">
                        <h4>Assessments</h4>
                        <div class="input-group">
                            <label for="mid_semester">Mid Semester (15):</label>
                            <input type="number" name="mid_semester" id="mid_semester" class="score-input" min="0" max="15" step="0.5">
                        </div>
                        <div class="input-group">
                            <label for="project">Project (20):</label>
                            <input type="number" name="project" id="project" class="score-input" min="0" max="20" step="0.5">
                        </div>
                    </div>

                    <div class="assessment-category">
                        <h4>Finals</h4>
                        <div class="input-group">
                            <label for="attendance">Attendance (5):</label>
                            <input type="number" name="attendance" id="attendance" class="score-input" min="0" max="5" step="0.5">
                        </div>
                        <div class="input-group">
                            <label for="exam">Exam (50):</label>
                            <input type="number" name="exam" id="exam" class="score-input" min="0" max="50" step="0.5">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Save & Calculate</button>
                    <button type="reset" class="btn btn-secondary">🔄 Reset</button>
                </div>
            </form>
        </div>

        <div class="courses-section">
            <div class="section-header">
                <h3><span class="section-icon">📚</span> Summary</h3>
            </div>

            <div class="table-responsive">
                <table class="courses-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Title</th>
                            <th>Unit</th>
                            <th>Scores (Q/M/P/A/E)</th>
                            <th>Total</th>
                            <th>Grade</th>
                            <th>GP</th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_grade_points = 0;
                        $total_credits = 0;
                        
                        if ($student_courses && mysqli_num_rows($student_courses) > 0) {
                            mysqli_data_seek($student_courses, 0); // Reset pointer again
                            while ($course = mysqli_fetch_assoc($student_courses)): 
                                $total = $course['total_score'] ?? 0;
                                $grade_point = $course['grade_point'] ?? 0;
                                $credit = $course['credit_unit'];
                                
                                // Only calculate GP if the course has been graded
                                if ($course['grade'] != '') {
                                    $total_grade_points += ($grade_point * $credit);
                                    $total_credits += $credit;
                                }
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_title']); ?></td>
                                <td><?php echo $credit; ?></td>
                                <td>
                                    <small>
                                        Q:<?php echo ($course['quiz_1']+$course['quiz_2']+$course['quiz_3']); ?> | 
                                        M:<?php echo $course['mid_semester'] ?? '-'; ?> | 
                                        P:<?php echo $course['project'] ?? '-'; ?> | 
                                        A:<?php echo $course['attendance'] ?? '-'; ?> | 
                                        E:<?php echo $course['exam'] ?? '-'; ?>
                                    </small>
                                </td>
                                <td class="<?php echo $total >= 50 ? 'text-success' : 'text-danger'; ?>">
                                    <strong><?php echo $total ? number_format($total, 1) : '-'; ?></strong>
                                </td>
                                <td><span class="grade-badge"><?php echo $course['grade'] ?? '-'; ?></span></td>
                                <td><?php echo $grade_point ? number_format($grade_point, 1) : '-'; ?></td>
                                <td>
                                    <?php 
                                        // Prepare data for JS safely
                                        $jsData = [
                                            'id' => $course['course_ref_id'], // Use the aliased ID
                                            'q1' => $course['quiz_1'],
                                            'q2' => $course['quiz_2'],
                                            'q3' => $course['quiz_3'],
                                            'mid' => $course['mid_semester'],
                                            'proj' => $course['project'],
                                            'att' => $course['attendance'],
                                            'exam' => $course['exam']
                                        ];
                                    ?>
                                    <button type="button" class="btn-icon-small" 
                                            onclick='editCourse(<?php echo json_encode($jsData); ?>)'>
                                        ✏️
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; 
                        } else { ?>
                            <tr><td colspan="8">No courses found for this student.</td></tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr class="summary-row">
                            <td colspan="3" class="summary-label">Cumulative GPA:</td>
                            <td colspan="5" class="summary-gpa">
                                <?php 
                                if ($total_credits > 0) {
                                    $cgpa = $total_grade_points / $total_credits;
                                    echo number_format($cgpa, 2);
                                } else {
                                    echo "0.00";
                                }
                                ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    <?php elseif ($selected_student_id > 0): ?>
        <div class="error-message">
            <p>Student not found.</p>
        </div>
    <?php else: ?>
        <div class="welcome-message">
            <h2>Welcome to GPA Calculator</h2>
            <p>Please select a student from the dropdown.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function editCourse(data) {
    // Populate form fields using the data object passed from PHP
    document.getElementById('course_id').value = data.id;
    document.getElementById('quiz_1').value = data.q1 || '';
    document.getElementById('quiz_2').value = data.q2 || '';
    document.getElementById('quiz_3').value = data.q3 || '';
    document.getElementById('mid_semester').value = data.mid || '';
    document.getElementById('project').value = data.proj || '';
    document.getElementById('attendance').value = data.att || '';
    document.getElementById('exam').value = data.exam || '';
    
    // Smooth scroll to the form
    document.getElementById('assessmentForm').scrollIntoView({ behavior: 'smooth' });
}
</script>

<?php include '../includes/footer.php'; ?>