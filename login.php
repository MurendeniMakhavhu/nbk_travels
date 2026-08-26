<?php
session_start();
require_once "config/db.php";

$loginMessage = "";
$stickyEmail = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'];
    $password = $_POST['password'];
    $stickyEmail = $email; // keep the email filled in if login fails

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $loginMessage = "No account found for that email. <a href='register.php'>Register here</a>.";
    } else {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password_hash'])) {

            if ($user['status'] === 'verified') {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];
                header("Location: dashboard.php");
                exit();
            } elseif ($user['status'] === 'pending') {
                $loginMessage = "Your account is still pending administrator verification.";
            } else {
                $loginMessage = "Your registration was rejected. Please contact NBK Travel.";
            }

        } else {
            $loginMessage = "Incorrect password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>

    <?php if ($loginMessage): ?>
        <p style="color:red;"><?php echo $loginMessage; ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="email">Email:</label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?php echo htmlspecialchars($stickyEmail); ?>"
            required
        >
        <br><br>
        <label for="password">Password:</label>
        <input
            type="password"
            id="password"
            name="password"
            minlength="8"
            required
        >
        <br><br>
        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register</a></p>
</body>
</html>