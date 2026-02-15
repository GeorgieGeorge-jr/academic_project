<?php
// Payroll page - Form to enter employee work details
require_once '../config/db.php';
include '../includes/header.php';
include '../includes/navbar.php';

// Fetch all employees for dropdown
$query = "SELECT id, name, position FROM employees ORDER BY name";
$result = mysqli_query($conn, $query);
?>

<div class="form-container">
    <h2>Employee Payroll Calculator</h2>
    
    <form action="payroll_process.php" method="POST">
        <!-- Employee Selection -->
        <div class="form-group">
            <label for="employee_id">Select Employee:</label>
            <select name="employee_id" id="employee_id" class="form-control" required>
                <option value="">-- Select Employee --</option>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <option value="<?php echo $row['id']; ?>">
                        <?php echo htmlspecialchars($row['name'] . ' - ' . $row['position']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <!-- Hours Worked -->
        <div class="form-group">
            <label for="hours_worked">Hours Worked:</label>
            <input type="number" name="hours_worked" id="hours_worked" 
                   class="form-control" step="0.5" min="0" required 
                   placeholder="Enter hours worked (e.g., 40)">
        </div>
        
        <!-- Hourly Rate -->
        <div class="form-group">
            <label for="hourly_rate">Hourly Rate ($):</label>
            <input type="number" name="hourly_rate" id="hourly_rate" 
                   class="form-control" step="0.01" min="0" required 
                   placeholder="Enter hourly rate (e.g., 25.50)">
        </div>
        
        <!-- Deduction -->
        <div class="form-group">
            <label for="deduction">Deduction ($):</label>
            <input type="number" name="deduction" id="deduction" 
                   class="form-control" step="0.01" min="0" required 
                   placeholder="Enter deduction amount">
        </div>
        
        <button type="submit" class="btn">Calculate Payroll</button>
    </form>
</div>

<?php
include '../includes/footer.php';
?>