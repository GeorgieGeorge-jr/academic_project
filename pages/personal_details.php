<?php
// Personal Details page - Display student profile with GPA
require_once '../config/db.php';
include '../includes/header.php';
include '../includes/navbar.php';

// Fetch all students for selection (optional - you could also use a specific student)
$students_query = "SELECT * FROM students ORDER BY name";
$students_result = mysqli_query($conn, $students_query);

// Load students into array safely
$students = [];
if ($students_result) {
    while ($row = mysqli_fetch_assoc($students_result)) {
        $students[] = $row;
    }
}

// Get selected student or default to first available student
$selected_student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : null;
if ($selected_student_id === null) {
    if (!empty($students)) {
        $selected_student_id = (int)$students[0]['id'];
    } else {
        $selected_student_id = 0; // no students
    }
}

// Fetch student details
$student = null;
if ($selected_student_id > 0) {
    $student_stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
    if ($student_stmt) {
        mysqli_stmt_bind_param($student_stmt, "i", $selected_student_id);
        mysqli_stmt_execute($student_stmt);
        $student_result = mysqli_stmt_get_result($student_stmt);
        $student = mysqli_fetch_assoc($student_result);
    }
}

// Fetch latest GPA for this student
$gpa_stmt = mysqli_prepare($conn, 
    "SELECT gpa, date_created FROM gpa_records 
     WHERE student_id = ? 
     ORDER BY date_created DESC LIMIT 1"
);
$gpa_record = null;
if ($selected_student_id > 0 && $gpa_stmt) {
    mysqli_stmt_bind_param($gpa_stmt, "i", $selected_student_id);
    mysqli_stmt_execute($gpa_stmt);
    $gpa_result = mysqli_stmt_get_result($gpa_stmt);
    $gpa_record = mysqli_fetch_assoc($gpa_result);
}
?>

<div style="margin-bottom: 2rem;">
    <h2>Student Personal Details</h2>
    
    <!-- Student selector -->
    <form method="GET" action="" style="margin: 2rem 0;">
        <div class="form-group" style="max-width: 300px;">
            <label for="student_id">Select Student:</label>
            <select name="student_id" id="student_id" class="form-control" onchange="this.form.submit()">
                <?php if (!empty($students)): ?>
                    <?php foreach ($students as $s): ?>
                        <option value="<?php echo (int)$s['id']; ?>" <?php echo $s['id'] == $selected_student_id ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="">No students available</option>
                <?php endif; ?>
            </select>
        </div>
    </form>
    
    <?php if ($student): ?>
    <!-- Profile Card -->
    <div class="profile-card">
        <div class="profile-header">
            <h2>Student Profile</h2>
            <p>Matric Number: <?php echo htmlspecialchars($student['matric_number']); ?></p>
        </div>
        
        <div class="profile-info">
            <span class="profile-label">Full Name:</span>
            <?php echo htmlspecialchars($student['name']); ?>
        </div>
        
        <div class="profile-info">
            <span class="profile-label">Blood Group:</span>
            <?php echo htmlspecialchars($student['blood_group'] ?: 'Not specified'); ?>
        </div>
        
        <div class="profile-info">
            <span class="profile-label">State of Origin:</span>
            <?php echo htmlspecialchars($student['state_of_origin'] ?: 'Not specified'); ?>
        </div>
        
        <div class="profile-info">
            <span class="profile-label">Phone Number:</span>
            <?php echo htmlspecialchars($student['phone'] ?: 'Not specified'); ?>
        </div>
        
        <div class="profile-info">
            <span class="profile-label">Hobbies:</span>
            <?php echo htmlspecialchars($student['hobbies'] ?: 'Not specified'); ?>
        </div>
        
        <div class="profile-info" style="background: #e8f0fe; border-radius: 4px;">
            <span class="profile-label">Current GPA:</span>
            <?php if ($gpa_record): ?>
                <strong><?php echo number_format($gpa_record['gpa'], 2); ?></strong>
                <small>(as of <?php echo date('Y-m-d', strtotime($gpa_record['date_created'])); ?>)</small>
            <?php else: ?>
                <em>No GPA calculated yet</em>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Academic Summary -->
    <div style="margin-top: 2rem;">
        <h3>Academic Summary</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Title</th>
                    <th>Credit Unit</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch courses for this student
                $total_credits = 0;
                if ($selected_student_id > 0) {
                    $courses_stmt = mysqli_prepare($conn,
                        "SELECT c.course_code, c.course_title, c.credit_unit
                         FROM courses c
                         JOIN student_courses sc ON c.id = sc.course_id
                         WHERE sc.student_id = ?
                         ORDER BY c.course_code"
                    );
                    if ($courses_stmt) {
                        mysqli_stmt_bind_param($courses_stmt, "i", $selected_student_id);
                        mysqli_stmt_execute($courses_stmt);
                        $courses_result = mysqli_stmt_get_result($courses_stmt);
                        while ($course = mysqli_fetch_assoc($courses_result)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($course['course_code']) . "</td>";
                            echo "<td>" . htmlspecialchars($course['course_title']) . "</td>";
                            echo "<td>" . htmlspecialchars($course['credit_unit']) . "</td>";
                            echo "</tr>";
                            $total_credits += $course['credit_unit'];
                        }
                    }
                }
                ?>
                <tr style="font-weight: bold; background: #f0f8ff;">
                    <td colspan="2" style="text-align: right;">Total Credit Units:</td>
                    <td><?php echo $total_credits; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <?php else: ?>
        <div class="alert alert-error">Student not found.</div>
    <?php endif; ?>
</div>

<?php
include '../includes/footer.php';
?>