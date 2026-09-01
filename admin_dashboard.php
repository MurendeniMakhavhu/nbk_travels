<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}
require_once "config/db.php";

$flashMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'verify') {
    $userId = $_POST['user_id'];

    $stmt = $conn->prepare("UPDATE users SET status = 'verified' WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $stmt2 = $conn->prepare("SELECT full_name, phone FROM users WHERE user_id = ?");
    $stmt2->bind_param("i", $userId);
    $stmt2->execute();
    $userData = $stmt2->get_result()->fetch_assoc();

    $check = $conn->prepare("SELECT cust_id FROM customers WHERE user_id = ?");
    $check->bind_param("i", $userId);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $insert = $conn->prepare(
            "INSERT INTO customers (user_id, full_name, phone_num) VALUES (?, ?, ?)"
        );
        $insert->bind_param("iss", $userId, $userData['full_name'], $userData['phone']);
        $insert->execute();
    }

    $flashMessage = "User verified and added as a customer.";
}

$result = $conn->query("SELECT * FROM users WHERE status = 'pending'");

require_once __DIR__ . '/includes/header.php';
?>

<h1>Admin Dashboard</h1>

<?php if ($flashMessage): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($flashMessage); ?></div>
<?php endif; ?>

<div class="card">
    <h2>Pending Registrations</h2>
    <table>
        <tr><th>Name</th><th>Email</th><th>Action</th></tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td>
                    <form method="POST" action="admin_dashboard.php" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                        <button type="submit" name="action" value="verify" style="padding:6px 14px;">Verify</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
