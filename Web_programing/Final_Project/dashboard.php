<?php
session_start();
require 'db.php';

if (!isset($_SESSION['persID'])) {
    header('Location: login.php');
    exit;
}

$deptStmt = $pdo->query('SELECT * FROM departments ORDER BY deptName');

$peopleStmt = $pdo->query(
    'SELECT p.*, d.deptName 
     FROM persons p
     INNER JOIN departments d ON p.persDept = d.deptID
     ORDER BY p.persLName, p.persFName'
);
$departments = $deptStmt->fetchAll();
$people = $peopleStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Directory</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="wrapper">
    <header>
        <h1>Directory Dashboard</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="register.php">Register New Faculty/Staff</a>
            <a href="login.php">Logout</a>
        </nav>
    </header>

    <main>
        <section>
            <h2>Departments</h2>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Department Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Office</th>
                    <th>Edit</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($departments as $dept): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($dept['deptID']); ?></td>
                        <td><?php echo htmlspecialchars($dept['deptName']); ?></td>
                        <td><?php echo htmlspecialchars($dept['deptPhone']); ?></td>
                        <td><?php echo htmlspecialchars($dept['deptEmail']); ?></td>
                        <td><?php echo htmlspecialchars($dept['deptOffice']); ?></td>
                        <td>
                            <a href="department.php?deptID=<?php echo urlencode($dept['deptID']); ?>">
                                Edit
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section>
            <h2>People in Directory</h2>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Phone</th>
                    <th>Office</th>
                    <th>Department</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($people as $pers): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pers['persID']); ?></td>
                        <td><?php echo htmlspecialchars($pers['persEmail']); ?></td>
                        <td><?php echo htmlspecialchars($pers['persFName']); ?></td>
                        <td><?php echo htmlspecialchars($pers['persLName']); ?></td>
                        <td><?php echo htmlspecialchars($pers['persPhone']); ?></td>
                        <td><?php echo htmlspecialchars($pers['persOffice']); ?></td>
                        <td><?php echo htmlspecialchars($pers['deptName']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        &copy; <?php echo date('Y'); ?> University Directory
    </footer>
</div>
</body>
</html>