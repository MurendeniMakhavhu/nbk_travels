<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

require_once "config/db.php";

$flashMessage = "";
$editingCustomer = null;

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $custId = $_POST['cust_id'];
    $stmt = $conn->prepare("DELETE FROM customers WHERE cust_id = ?");
    $stmt->bind_param("i", $custId);
    $stmt->execute();
    $flashMessage = "Customer deleted.";
}

// ADD or UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['add', 'update'])) {
    $fullName = $_POST['full_name'];
    $phone = $_POST['phone_num'];
    $preferences = $_POST['preferences'] ?? '';

    if ($_POST['action'] === 'add') {
        $stmt = $conn->prepare(
            "INSERT INTO customers (full_name, phone_num, preferences) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $fullName, $phone, $preferences);
        $stmt->execute();
        $flashMessage = "Customer added.";
    } else {
        $custId = $_POST['cust_id'];
        $stmt = $conn->prepare(
            "UPDATE customers SET full_name = ?, phone_num = ?, preferences = ? WHERE cust_id = ?"
        );
        $stmt->bind_param("sssi", $fullName, $phone, $preferences, $custId);
        $stmt->execute();
        $flashMessage = "Customer updated.";
    }
}

// Load a customer into the form for editing
if (isset($_GET['edit'])) {
    $custId = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM customers WHERE cust_id = ?");
    $stmt->bind_param("i", $custId);
    $stmt->execute();
    $editingCustomer = $stmt->get_result()->fetch_assoc();
}

$customers = $conn->query("SELECT * FROM customers ORDER BY cust_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers</title>
</head>
<body>
    <h1>Manage Customers</h1>

    <?php if ($flashMessage): ?>
        <p style="color:green;"><?php echo htmlspecialchars($flashMessage); ?></p>
    <?php endif; ?>

    <h2><?php echo $editingCustomer ? 'Update Customer' : 'Add New Customer'; ?></h2>
    <form method="POST" action="admin_customers.php">
        <input type="hidden" name="action" value="<?php echo $editingCustomer ? 'update' : 'add'; ?>">
        <?php if ($editingCustomer): ?>
            <input type="hidden" name="cust_id" value="<?php echo $editingCustomer['cust_id']; ?>">
        <?php endif; ?>

        <label>Full Name:
            <input type="text" name="full_name" required
                   value="<?php echo htmlspecialchars($editingCustomer['full_name'] ?? ''); ?>">
        </label><br><br>

        <label>Phone:
            <input type="text" name="phone_num" required
                   value="<?php echo htmlspecialchars($editingCustomer['phone_num'] ?? ''); ?>">
        </label><br><br>

        <label>Preferences:
            <input type="text" name="preferences"
                   value="<?php echo htmlspecialchars($editingCustomer['preferences'] ?? ''); ?>">
        </label><br><br>

        <button type="submit"><?php echo $editingCustomer ? 'Save Changes' : 'Add Customer'; ?></button>
    </form>
    <?php if ($editingCustomer): ?>
        <p><a href="admin_customers.php">Cancel edit</a></p>
    <?php endif; ?>

    <h2>All Customers</h2>
    <table border="1">
        <tr>
            <th>ID</th><th>Name</th><th>Phone</th><th>Preferences</th><th>Actions</th>
        </tr>
        <?php while ($row = $customers->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['cust_id']; ?></td>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['phone_num']); ?></td>
                <td><?php echo htmlspecialchars($row['preferences']); ?></td>
                <td>
                    <a href="admin_customers.php?edit=<?php echo $row['cust_id']; ?>">Edit</a>
                    &nbsp;
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this customer?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="cust_id" value="<?php echo $row['cust_id']; ?>">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <p><a href="admin_dashboard.php">&larr; Back to Dashboard</a></p>
    <p><a href="admin_logout.php">Log out</a></p>
</body>
</html>