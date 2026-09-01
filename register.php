<?php
session_start();
require_once __DIR__ . '/config/db.php';

$errorMsg = "";
$successMsg = "";
$sticky = ['full_name' => '', 'email' => '', 'phone' => ''];

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
        $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $errorMsg = "An account with that email already exists.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $conn->prepare(
                "INSERT INTO users (full_name, email, phone, password_hash, role, status)
                 VALUES (?, ?, ?, ?, 'customer', 'pending')"
            );
            $insertStmt->bind_param("ssss", $fullName, $email, $phone, $passwordHash);
            $insertStmt->execute();

            $successMsg = "Registration successful! Your account is pending administrator verification.";
            $sticky = ['full_name' => '', 'email' => '', 'phone' => ''];
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="card" style="max-width:460px; margin:0 auto;">
    <h1>Register</h1>

    <?php if ($errorMsg): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($errorMsg); ?></div>
    <?php endif; ?>
    <?php if ($successMsg): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMsg); ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" required minlength="2"
               value="<?php echo htmlspecialchars($sticky['full_name']); ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required
               value="<?php echo htmlspecialchars($sticky['email']); ?>">

        <label for="phone">Phone</label>
        <input type="tel" id="phone" name="phone" required
               value="<?php echo htmlspecialchars($sticky['phone']); ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="8">

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">

        <button type="submit">Register</button>
    </form>

    <p style="margin-top:1rem;"><a href="login.php">Already have an account? Login</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
