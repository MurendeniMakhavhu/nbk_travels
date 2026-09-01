<?php
session_start();
require_once __DIR__ . '/config/db.php';

$loggedInUser = null;
$errorMsg = null;
$showRegisterLink = false;
$stickyUsername = '';

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer') {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stickyUsername = $email;

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $errorMsg = "No account found for that email.";
        $showRegisterLink = true;
    } else {
        $user = $result->fetch_assoc();

        if (!password_verify($password, $user['password_hash'])) {
            $errorMsg = "Incorrect password. Please try again.";
        } elseif ($user['status'] === 'pending') {
            $errorMsg = "Your account is still pending administrator verification.";
        } elseif ($user['status'] === 'rejected') {
            $errorMsg = "Your registration was rejected. Please contact NBK Travel.";
        } elseif ($user['role'] !== 'customer') {
            $errorMsg = "This account must sign in through the Admin login.";
        } else {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = 'customer';
            header("Location: dashboard.php");
            exit();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="card" style="max-width:420px; margin:0 auto;">
    <h1>Customer Login</h1>

    <?php if ($errorMsg): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php if ($showRegisterLink): ?>
            <p>Don't have an account? <a href="register.php">Register here</a>.</p>
        <?php endif; ?>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required minlength="3"
               value="<?php echo htmlspecialchars($stickyUsername); ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="8">

        <button type="submit">Login</button>
    </form>

    <p style="margin-top:1rem;">Don't have an account? <a href="register.php">Register</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
