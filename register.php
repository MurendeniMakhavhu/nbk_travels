<?php
session_start();
require_once "config/db.php";

$errorMsg = "";
$successMsg = "";

// Sticky values so the user doesn't retype everything if something goes wrong
$sticky = [
    'full_name' => '',
    'email' => '',
    'phone' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    $sticky = ['full_name' => $fullName, 'email' => $email, 'phone' => $phone];

    if ($fullName === '' || $email === '' || $phone === '' || $password === '') {
        $errorMsg = "Please fill in all fields.";
    } elseif ($password !== $confirmPassword) {
        $errorMsg = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $errorMsg = "Password must be at least 8 characters.";
    } else {

        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $errorMsg = "An account with that email already exists.";
        } else {
            // Hash the password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user — role defaults to 'customer', status defaults to 'pending'
            $insertStmt = $conn->prepare(
                "INSERT INTO users (full_name, email, phone, password_hash, role, status)
                 VALUES (?, ?, ?, ?, 'customer', 'pending')"
            );
            $insertStmt->bind_param("ssss", $fullName, $email, $phone, $passwordHash);
            $insertStmt->execute();

            $successMsg = "Registration successful! Your account is pending administrator verification. You will be able to log in once approved.";
            $sticky = ['full_name' => '', 'email' => '', 'phone' => '']; // clear form on success
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h1>Register</h1>

    <?php if ($errorMsg): ?>
        <p style="color:red;"><?php echo htmlspecialchars($errorMsg); ?></p>
    <?php endif; ?>

    <?php if ($successMsg): ?>
        <p style="color:green;"><?php echo htmlspecialchars($successMsg); ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label for="full_name">Full Name:</label><br>
        <input type="text" id="full_name" name="full_name" required minlength="2"
               value="<?php echo htmlspecialchars($sticky['full_name']); ?>">
        <br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required
               value="<?php echo htmlspecialchars($sticky['email']); ?>">
        <br><br>

        <label for="phone">Phone:</label><br>
        <input type="tel" id="phone" name="phone" required
               value="<?php echo htmlspecialchars($sticky['phone']); ?>">
        <br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required minlength="8">
        <br><br>

        <label for="confirm_password">Confirm Password:</label><br>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
        <br><br>

        <button type="submit">Register</button>
    </form>

    <p><a href="login.php">Already have an account? Login</a></p>
</body>
</html>