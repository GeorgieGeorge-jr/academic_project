<?php
// Process payroll calculation and display payslip
require_once '../config/db.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: payroll.php');
    exit();
}

// Get and validate form data
$employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
$hours_worked = filter_input(INPUT_POST, 'hours_worked', FILTER_VALIDATE_FLOAT);
$hourly_rate = filter_input(INPUT_POST, 'hourly_rate', FILTER_VALIDATE_FLOAT);
$deduction = filter_input(INPUT_POST, 'deduction', FILTER_VALIDATE_FLOAT);

// Validate all inputs
$errors = [];
if (!$employee_id) $errors[] = "Please select a valid employee.";
if (!$hours_worked || $hours_worked <= 0) $errors[] = "Please enter valid hours worked.";
if (!$hourly_rate || $hourly_rate <= 0) $errors[] = "Please enter valid hourly rate.";
if ($deduction === false || $deduction < 0) $errors[] = "Please enter valid deduction amount.";

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
                <span class="payslip-value"><?php echo htmlspecialchars($employee['name']); ?></span>
            </div>
            <div class="payslip-row">
                <span class="payslip-label">Position:</span>
                <span class="payslip-value"><?php echo htmlspecialchars($employee['position']); ?></span>
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
?>