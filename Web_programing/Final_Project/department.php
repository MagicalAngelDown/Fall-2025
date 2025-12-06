<?php
session_start();
require 'db.php';

if (!isset($_SESSION['persID'])) {
    header('Location: login.php');
    exit;
}

$deptID = $_GET['deptID'] ?? null;

if (!$deptID) {
    die('No department specified.');
}

$updateError = '';
$updateSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deptName = trim($_POST['deptName'] ?? '');
    $deptPhone = trim($_POST['deptPhone'] ?? '');
    $deptEmail = trim($_POST['deptEmail'] ?? '');
    $deptOffice = trim($_POST['deptOffice'] ?? '');

    if ($deptName === '') {
        $updateError = 'Department name is required.';
    } else {
        $stmt = $pdo->prepare(
            'UPDATE departments
             SET deptName = ?, deptPhone = ?, deptEmail = ?, deptOffice = ?
             WHERE deptID = ?'
        );
        $stmt->execute([$deptName, $deptPhone, $deptEmail, $deptOffice, $deptID]);

        // Redirect back to dashboard on success
        header('Location: dashboard.php');
        exit;
    }
}

$stmt = $pdo->prepare('SELECT * FROM departments WHERE deptID = ?');
$stmt->execute([$deptID]);
$department = $stmt->fetch();

if (!$department) {
    die('Department not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Department</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="wrapper">
    <header>
        <h1>Edit Department</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="login.php">Logout</a>
        </nav>
    </header>

    <main>
        <?php if ($updateError): ?>
            <p class="error"><?php echo htmlspecialchars($updateError); ?></p>
        <?php endif; ?>

        <form method="post" action="department.php?deptID=<?php echo urlencode($deptID); ?>" novalidate>
            <div class="form-row">
                <label for="deptID">Department ID:</label>
                <input type="text" id="deptID" value="<?php echo htmlspecialchars($department['deptID']); ?>" disabled>
            </div>

            <div class="form-row">
                <label for="deptName">Name:</label>
                <input type="text" name="deptName" id="deptName"
                       value="<?php echo htmlspecialchars($department['deptName']); ?>">
            </div>

            <div class="form-row">
                <label for="deptPhone">Phone:</label>
                <input type="text" name="deptPhone" id="deptPhone"
                       value="<?php echo htmlspecialchars($department['deptPhone']); ?>">
            </div>

            <div class="form-row">
                <label for="deptEmail">Email:</label>
                <input type="text" name="deptEmail" id="deptEmail"
                       value="<?php echo htmlspecialchars($department['deptEmail']); ?>">
            </div>

            <div class="form-row">
                <label for="deptOffice">Office:</label>
                <input type="text" name="deptOffice" id="deptOffice"
                       value="<?php echo htmlspecialchars($department['deptOffice']); ?>">
            </div>

            <div class="form-row">
                <label></label>
                <button type="submit">Update</button>
            </div>
        </form>
    </main>

    <footer>
        &copy; <?php echo date('Y'); ?> University Directory
    </footer>
</div>
</body>
</html>