<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}
require_once "config/db.php";

$flashMessage = "";
$editingDriver = null;

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $driverId = $_POST['driver_id'];
    $stmt = $conn->prepare("DELETE FROM drivers WHERE driver_id = ?");
    $stmt->bind_param("i", $driverId);
    $stmt->execute();
    $flashMessage = "Driver deleted.";
}

// ADD or UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['add', 'update'])) {
    $fullName = $_POST['full_name'];
    $phone = $_POST['phone_num'];
    $availability = $_POST['availability'];

    if ($_POST['action'] === 'add') {
        $stmt = $conn->prepare(
            "INSERT INTO drivers (full_name, phone_num, availability) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $fullName, $phone, $availability);
        $stmt->execute();
        $flashMessage = "Driver added.";
    } else {
        $driverId = $_POST['driver_id'];
        $stmt = $conn->prepare(
            "UPDATE drivers SET full_name = ?, phone_num = ?, availability = ? WHERE driver_id = ?"
        );
        $stmt->bind_param("sssi", $fullName, $phone, $availability, $driverId);
        $stmt->execute();
        $flashMessage = "Driver updated.";
    }
}

// Load a driver into the form for editing
if (isset($_GET['edit'])) {
    $driverId = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM drivers WHERE driver_id = ?");
    $stmt->bind_param("i", $driverId);
    $stmt->execute();
    $editingDriver = $stmt->get_result()->fetch_assoc();
}

$drivers = $conn->query("SELECT * FROM drivers ORDER BY driver_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Drivers</title>
</head>
<body>
    <h1>Manage Drivers</h1>

    <?php if ($flashMessage): ?>
        <p style="color:green;"><?php echo htmlspecialchars($flashMessage); ?></p>
    <?php endif; ?>

    <h2><?php echo $editingDriver ? 'Update Driver' : 'Add New Driver'; ?></h2>
    <form method="POST" action="admin_drivers.php">
        <input type="hidden" name="action" value="<?php echo $editingDriver ? 'update' : 'add'; ?>">
        <?php if ($editingDriver): ?>
            <input type="hidden" name="driver_id" value="<?php echo $editingDriver['driver_id']; ?>">
        <?php endif; ?>

        <label>Full Name:
            <input type="text" name="full_name" required
                   value="<?php echo htmlspecialchars($editingDriver['full_name'] ?? ''); ?>">
        </label><br><br>

        <label>Phone:
            <input type="text" name="phone_num" required
                   value="<?php echo htmlspecialchars($editingDriver['phone_num'] ?? ''); ?>">
        </label><br><br>

        <label>Availability:
            <select name="availability">
                <option value="available" <?php echo (isset($editingDriver) && $editingDriver['availability'] === 'available') ? 'selected' : ''; ?>>Available</option>
                <option value="unavailable" <?php echo (isset($editingDriver) && $editingDriver['availability'] === 'unavailable') ? 'selected' : ''; ?>>Unavailable</option>
            </select>
        </label><br><br>

        <button type="submit"><?php echo $editingDriver ? 'Save Changes' : 'Add Driver'; ?></button>
    </form>
    <?php if ($editingDriver): ?>
        <p><a href="admin_drivers.php">Cancel edit</a></p>
    <?php endif; ?>

    <h2>All Drivers</h2>
    <table border="1">
        <tr>
            <th>ID</th><th>Name</th><th>Phone</th><th>Availability</th><th>Actions</th>
        </tr>
        <?php while ($row = $drivers->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['driver_id']; ?></td>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['phone_num']); ?></td>
                <td><?php echo htmlspecialchars($row['availability']); ?></td>
                <td>
                    <a href="admin_drivers.php?edit=<?php echo $row['driver_id']; ?>">Edit</a>
                    &nbsp;
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this driver?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="driver_id" value="<?php echo $row['driver_id']; ?>">
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