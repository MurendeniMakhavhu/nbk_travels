<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . '/includes/header.php';
?>

<h1>Welcome!</h1>
<div class="card">
    <p style="margin-bottom:1rem;">What would you like to do?</p>
    <a href="book.php" class="btn">Book a Shuttle</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
