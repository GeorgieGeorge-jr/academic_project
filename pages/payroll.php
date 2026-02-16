<?php
// Process payroll calculation and display payslip
require_once '../config/db.php';

// Common header/navbar for both GET and POST
include '../includes/header.php';
include '../includes/navbar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and validate form data
    $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
    $hours_worked = filter_input(INPUT_POST, 'hours_worked', FILTER_VALIDATE_FLOAT);
    $hourly_rate = filter_input(INPUT_POST, 'hourly_rate', FILTER_VALIDATE_FLOAT);
    $deduction = filter_input(INPUT_POST, 'deduction', FILTER_VALIDATE_FLOAT);

    // Validate all inputs
    $errors = [];
    if (!$employee_id) $errors[] = "Please select a valid employee.";
    if ($hours_worked === false || $hours_worked <= 0) $errors[] = "Please enter valid hours worked.";
    if ($hourly_rate === false || $hourly_rate <= 0) $errors[] = "Please enter valid hourly rate.";
    if ($deduction === false || $deduction < 0) $errors[] = "Please enter valid deduction amount.";

    if (!empty($errors)) {
        // Display errors
        echo '<div class="form-container">';
        echo '<div class="alert alert-error">';
        foreach ($errors as $error) {
            echo '<p>' . htmlspecialchars($error) . '</p>';
        }
        echo '</div>';
        echo '<a href="payroll.php" class="btn">Go Back</a>';
        echo '</div>';
    } else {
        // Calculate payroll
        $gross_pay = $hours_worked * $hourly_rate;
        $net_pay = $gross_pay - $deduction;
        
        // Get employee details
        $stmt = mysqli_prepare($conn, "SELECT name, position FROM employees WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $employee_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $employee = mysqli_fetch_assoc($result);
        
        // Save to database using prepared statement
        $insert_stmt = mysqli_prepare($conn, 
            "INSERT INTO payroll_records (employee_id, hours_worked, hourly_rate, deduction, gross_pay, net_pay) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($insert_stmt, "iddddd", 
            $employee_id, $hours_worked, $hourly_rate, $deduction, $gross_pay, $net_pay
        );
        mysqli_stmt_execute($insert_stmt);
        $record_id = mysqli_insert_id($conn);
        
        // Display professional payslip
        ?>
        <div class="payslip">
            <div class="payslip-header">
                <h2>ACADEMIC INSTITUTE</h2>
                <h3>Employee Payslip</h3>
                <p>Date: <?php echo date('F j, Y'); ?></p>
            </div>
            
            <div class="payslip-details">
                <div class="payslip-row">
                    <span class="payslip-label">Employee Name:</span>
                    <span class="payslip-value"><?php echo htmlspecialchars($employee['name'] ?? 'Unknown'); ?></span>
                </div>
                <div class="payslip-row">
                    <span class="payslip-label">Position:</span>
                    <span class="payslip-value"><?php echo htmlspecialchars($employee['position'] ?? ''); ?></span>
                </div>
                <div class="payslip-row">
                    <span class="payslip-label">Hours Worked:</span>
                    <span class="payslip-value"><?php echo htmlspecialchars($hours_worked); ?> hours</span>
                </div>
                <div class="payslip-row">
                    <span class="payslip-label">Hourly Rate:</span>
                    <span class="payslip-value">$<?php echo number_format($hourly_rate, 2); ?></span>
                </div>
                <div class="payslip-row">
                    <span class="payslip-label">Gross Pay:</span>
                    <span class="payslip-value">$<?php echo number_format($gross_pay, 2); ?></span>
                </div>
                <div class="payslip-row">
                    <span class="payslip-label">Deduction:</span>
                    <span class="payslip-value">$<?php echo number_format($deduction, 2); ?></span>
                </div>
                <div class="payslip-total">
                    <div class="payslip-row">
                        <span class="payslip-label">NET PAY:</span>
                        <span class="payslip-value"><strong>$<?php echo number_format($net_pay, 2); ?></strong></span>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 2rem;">
                <p>This is a computer-generated document. No signature required.</p>
                <a href="payroll.php" class="btn">Calculate Another</a>
                <button onclick="window.print()" class="btn">Print Payslip</button>
            </div>
        </div>
        <?php
    }

    include '../includes/footer.php';

} else {
    // GET request: show payroll input form so the page opens when clicked
    // Fetch employees for the select box
    $employees = [];
    $res = mysqli_query($conn, "SELECT id, name, position FROM employees ORDER BY name ASC");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $employees[] = $row;
        }
    }

    ?>
    <div class="form-container">
        <h2 style="margin-bottom:1rem;">Payroll Calculator</h2>
        <form method="post" action="payroll.php" class="payroll-form">
            <div class="form-group">
                <label for="employee_id">Employee</label>
                <select name="employee_id" id="employee_id" class="form-control" required>
                    <option value="">-- Select Employee --</option>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?php echo (int)$emp['id']; ?>"><?php echo htmlspecialchars($emp['name'] . ' — ' . $emp['position']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="hours_worked">Hours Worked</label>
                <input type="number" step="0.01" min="0" name="hours_worked" id="hours_worked" class="form-control" required />
            </div>

            <div class="form-group">
                <label for="hourly_rate">Hourly Rate</label>
                <input type="number" step="0.01" min="0" name="hourly_rate" id="hourly_rate" class="form-control" required />
            </div>

            <div class="form-group">
                <label for="deduction">Deduction</label>
                <input type="number" step="0.01" min="0" name="deduction" id="deduction" class="form-control" value="0" required />
            </div>

            <div class="form-group" style="text-align:right; margin-top:0.5rem;">
                <button type="submit" class="btn">Calculate Payslip</button>
            </div>
        </form>
    </div>
    <?php

    include '../includes/footer.php';
}
?>