<!-- Navigation bar - appears on all pages -->
<nav class="navbar">
    <div class="nav-brand">
        <h2>📚 Academic Project</h2>
    </div>
    <ul class="nav-menu">
        <li><a href="/academic_project/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a></li>
        <li><a href="/academic_project/pages/payroll.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'payroll.php' ? 'active' : ''; ?>">Payroll</a></li>
        <li><a href="/academic_project/pages/gpa.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'gpa.php' ? 'active' : ''; ?>">GPA</a></li>
        <li><a href="/academic_project/pages/personal_details.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'personal_details.php' ? 'active' : ''; ?>">Personal Details</a></li>
    </ul>
</nav>