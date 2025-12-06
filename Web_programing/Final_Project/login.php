<?php
session_start();
require 'db.php';

$emailError = '';
$passwordError = '';
$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['persEmail'] ?? '');
    $password = $_POST['persPassword'] ?? '';

    if ($email === '') {
        $emailError = 'Email is required.';
    }
    if ($password === '') {
        $passwordError = 'Password is required.';
    }

    if ($emailError === '' && $passwordError === '') {
        $stmt = $pdo->prepare('SELECT * FROM persons WHERE persEmail = ? AND persPassword = ?');
        $stmt->execute([$email, $password]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['persID'] = $user['persID'];
            $_SESSION['persEmail'] = $user['persEmail'];
            header('Location: dashboard.php');
            exit;
        } else {
            $loginError = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Directory Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="wrapper">
    <header>
        <h1>Faculty/Staff Directory - Login</h1>
        <nav>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </nav>
    </header>

    <main>
        <?php if ($loginError): ?>
            <p class="error"><?php echo htmlspecialchars($loginError); ?></p>
        <?php endif; ?>

        <form method="post" action="login.php" novalidate>
            <div class="form-row">
                <label for="persEmail">Email:</label>
                <input type="email" name="persEmail" id="persEmail"
                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                <span class="error"><?php echo htmlspecialchars($emailError); ?></span>
            </div>
            <div class="form-row">
                <label for="persPassword">Password:</label>
                <input type="password" name="persPassword" id="persPassword">
                <span class="error"><?php echo htmlspecialchars($passwordError); ?></span>
            </div>
            <div class="form-row">
                <label></label>
                <button type="submit">Login</button>
            </div>
        </form>

        <p>If you do not have an account, please <a href="register.php">register</a>.</p>
    </main>

    <footer>
        &copy; <?php echo date('Y'); ?> University Directory
    </footer>
</div>
</body>
</html>