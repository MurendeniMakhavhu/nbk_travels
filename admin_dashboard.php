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

    // 1. Update the user's status
    $stmt = $conn->prepare("UPDATE users SET status = 'verified' WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    // 2. Fetch that user's details so we can copy them into customers
    $stmt2 = $conn->prepare("SELECT full_name, phone FROM users WHERE user_id = ?");
    $stmt2->bind_param("i", $userId);
    $stmt2->execute();
    $userData = $stmt2->get_result()->fetch_assoc();

    // 3. Only insert into customers if this user doesn't already have one
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

$result = $conn->query(
    "SELECT * FROM users WHERE status = 'pending'"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Admin Dashboard</h1>

    <?php if ($flashMessage): ?>
        <p style="color:green;"><?php echo htmlspecialchars($flashMessage); ?></p>
    <?php endif; ?>

    <h2>Pending Registrations</h2>
    <table border="1">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td>
                    <form method="POST" action="admin_dashboard.php">
                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                        <button type="submit" name="action" value="verify">
                            Verify
                        </button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <p><a href="admin_customers.php">Manage Customers &rarr;</a></p>
    <p><a href="admin_drivers.php">Manage Drivers &rarr;</a></p>
    <p><a href="admin_vehicles.php">Manage Vehicles &rarr;</a></p>
    <p><a href="admin_bookings.php">View All Bookings &rarr;</a></p>
    <p><a href="admin_logout.php">Log out</a></p>
</body>
</html>