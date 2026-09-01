<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit;
}

$errorMsg = null;
$stickyEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stickyEmail = $email;

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $errorMsg = "Admin account not found.";
    } else {
        $user = $result->fetch_assoc();
        if (!password_verify($password, $user['password_hash'])) {
            $errorMsg = "Incorrect password.";
        } elseif ($user['status'] !== 'verified') {
            $errorMsg = "Your admin account has not been verified.";
        } else {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            header("Location: admin_dashboard.php");
            exit;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="card" style="max-width:420px; margin:0 auto;">
    <h1>Admin Login</h1>

    <?php if ($errorMsg): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($errorMsg); ?></div>
    <?php endif; ?>

    <form method="POST" action="admin_login.php">
        <label for="email">Admin Email</label>
        <input type="email" id="email" name="email" required minlength="3"
               value="<?php echo htmlspecialchars($stickyEmail); ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="8">

        <button type="submit">Login as Admin</button>
    </form>

    <p style="margin-top:1rem;"><a href="login.php">&larr; Back to customer login</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>