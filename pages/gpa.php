<?php
// GPA page - Enter course scores to calculate GPA
require_once '../config/db.php';
include '../includes/header.php';
include '../includes/navbar.php';

// Fetch all students for dropdown
$students_query = "SELECT id, name, matric_number FROM students ORDER BY name";
$students_result = mysqli_query($conn, $students_query);
?>

<div class="form-container">
    <h2>GPA Calculator</h2>
    
    <form action="gpa_process.php" method="POST">
        <!-- Student Selection -->
        <div class="form-group">
            <label for="student_id">Select Student:</label>
            <select name="student_id" id="student_id" class="form-control" required>
                <option value="">-- Select Student --</option>
                <?php while ($student = mysqli_fetch_assoc($students_result)): ?>
                    <option value="<?php echo $student['id']; ?>">
                        <?php echo htmlspecialchars($student['name'] . ' (' . $student['matric_number'] . ')'); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <!-- Course Details -->
        <div class="form-group">
            <label for="course">Course Name:</label>
            <input type="text" name="course" id="course" class="form-control" 
                   placeholder="e.g., Introduction to Programming" required>
        </div>
        
        <div class="form-group">
            <label for="score">Score (0-100):</label>
            <input type="number" name="score" id="score" class="form-control" 
                   min="0" max="100" step="0.5" placeholder="Enter your score" required>
        </div>
        
        <div class="form-group">
            <label for="credit_unit">Credit Unit:</label>
            <input type="number" name="credit_unit" id="credit_unit" class="form-control" 
                   min="1" max="5" placeholder="e.g., 3" required>
        </div>
        
        <button type="submit" class="btn">Calculate GPA</button>
    </form>
</div>

<!-- Display existing GPA records -->
<div style="margin-top: 3rem;">
    <h3>Student GPA Records</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Matric Number</th>
                <th>Total Grade Points</th>
                <th>Total Credit Units</th>
                <th>GPA</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch all GPA records with student names
            $gpa_query = "SELECT s.name, s.matric_number, g.* 
                         FROM gpa_records g
                         JOIN students s ON g.student_id = s.id
                         ORDER BY g.date_created DESC";
            $gpa_result = mysqli_query($conn, $gpa_query);
            
            while ($record = mysqli_fetch_assoc($gpa_result)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($record['name']) . "</td>";
                echo "<td>" . htmlspecialchars($record['matric_number']) . "</td>";
                echo "<td>" . htmlspecialchars($record['total_grade_points']) . "</td>";
                echo "<td>" . htmlspecialchars($record['total_credit_units']) . "</td>";
                echo "<td><strong>" . number_format($record['gpa'], 2) . "</strong></td>";
                echo "<td>" . date('Y-m-d', strtotime($record['date_created'])) . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php
include '../includes/footer.php';
?>