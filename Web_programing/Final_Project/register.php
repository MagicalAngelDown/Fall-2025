<?php
session_start();
require 'db.php';

$successMessage = '';
$serverErrors = [];

$deptStmt = $pdo->query('SELECT deptID, deptName FROM departments ORDER BY deptName');
$departments = $deptStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $persEmail = trim($_POST['persEmail'] ?? '');
    $persPassword = $_POST['persPassword'] ?? '';
    $persFName = trim($_POST['persFName'] ?? '');
    $persLName = trim($_POST['persLName'] ?? '');
    $persPhone = trim($_POST['persPhone'] ?? '');
    $persOffice = trim($_POST['persOffice'] ?? '');
    $persDept = $_POST['persDept'] ?? '';

    if ($persEmail === '') {
        $serverErrors[] = 'Email is required.';
    }
    if ($persPassword === '' || strlen($persPassword) < 8) {
        $serverErrors[] = 'Password is required and must be at least 8 characters.';
    }
    if ($persFName === '') {
        $serverErrors[] = 'First name is required.';
    }
    if ($persLName === '') {
        $serverErrors[] = 'Last name is required.';
    }
    if ($persPhone === '') {
        $serverErrors[] = 'Phone is required.';
    }
    if ($persOffice === '') {
        $serverErrors[] = 'Office location is required.';
    }
    if ($persDept === '') {
        $serverErrors[] = 'Department is required.';
    }

    if (empty($serverErrors)) {
        $stmt = $pdo->prepare(
            'INSERT INTO persons 
            (persEmail, persPassword, persFName, persLName, persPhone, persOffice, persDept)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $persEmail,
            $persPassword,
            $persFName,
            $persLName,
            $persPhone,
            $persOffice,
            $persDept
        ]);

        $successMessage = 'Registration successful. You may now <a href="login.php">login</a>.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Faculty/Staff</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/validation.js" defer></script>
</head>
<body>
<div class="wrapper">
    <header>
        <h1>Faculty/Staff Registration</h1>
        <nav>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </nav>
    </header>

    <main>
        <?php if (!empty($serverErrors)): ?>
            <ul class="error">
                <?php foreach ($serverErrors as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($successMessage): ?>
            <p class="success"><?php echo $successMessage; ?></p>
        <?php endif; ?>

        <form id="registerForm" method="post" action="register.php" novalidate>
            <div class="form-row">
                <label for="persEmail">Email Address:</label>
                <input type="text" name="persEmail" id="persEmail"
                       value="<?php echo isset($persEmail) ? htmlspecialchars($persEmail) : ''; ?>">
                <span class="error" id="emailError"></span>
            </div>

            <div class="form-row">
                <label for="persPassword">Password:</label>
                <input type="password" name="persPassword" id="persPassword">
                <span class="error" id="passwordError"></span>
            </div>

            <div class="form-row">
                <label for="persFName">First Name:</label>
                <input type="text" name="persFName" id="persFName"
                       value="<?php echo isset($persFName) ? htmlspecialchars($persFName) : ''; ?>">
                <span class="error" id="fNameError"></span>
            </div>

            <div class="form-row">
                <label for="persLName">Last Name:</label>
                <input type="text" name="persLName" id="persLName"
                       value="<?php echo isset($persLName) ? htmlspecialchars($persLName) : ''; ?>">
                <span class="error" id="lNameError"></span>
            </div>

            <div class="form-row">
                <label for="persPhone">Phone Number:</label>
                <input type="text" name="persPhone" id="persPhone"
                       value="<?php echo isset($persPhone) ? htmlspecialchars($persPhone) : ''; ?>">
                <span class="error" id="phoneError"></span>
            </div>

            <div class="form-row">
                <label for="persOffice">Office Location:</label>
                <input type="text" name="persOffice" id="persOffice"
                       value="<?php echo isset($persOffice) ? htmlspecialchars($persOffice) : ''; ?>">
                <span class="error" id="officeError"></span>
            </div>

            <div class="form-row">
                <label for="persDept">Department:</label>
                <select name="persDept" id="persDept">
                    <option value="">-- Select Department --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept['deptID']); ?>"
                            <?php
                            if (isset($persDept) && $persDept == $dept['deptID']) {
                                echo 'selected';
                            }
                            ?>>
                            <?php echo htmlspecialchars($dept['deptName']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="error" id="deptError"></span>
            </div>

            <div class="form-row">
                <label></label>
                <button type="submit">Submit</button>
            </div>
        </form>
    </main>

    <footer>
        &copy; <?php echo date('Y'); ?> University Directory
    </footer>
</div>
</body>
</html>