<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}
require_once "config/db.php";

$flashMessage = "";
$editingDriver = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $driverId = $_POST['driver_id'];
    $stmt = $conn->prepare("DELETE FROM drivers WHERE driver_id = ?");
    $stmt->bind_param("i", $driverId);
    $stmt->execute();
    $flashMessage = "Driver deleted.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['add', 'update'])) {
    $fullName = $_POST['full_name'];
    $phone = $_POST['phone_num'];
    $availability = $_POST['availability'];

    if ($_POST['action'] === 'add') {
        $stmt = $conn->prepare("INSERT INTO drivers (full_name, phone_num, availability) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fullName, $phone, $availability);
        $stmt->execute();
        $flashMessage = "Driver added.";
    } else {
        $driverId = $_POST['driver_id'];
        $stmt = $conn->prepare("UPDATE drivers SET full_name = ?, phone_num = ?, availability = ? WHERE driver_id = ?");
        $stmt->bind_param("sssi", $fullName, $phone, $availability, $driverId);
        $stmt->execute();
        $flashMessage = "Driver updated.";
    }
}

if (isset($_GET['edit'])) {
    $driverId = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM drivers WHERE driver_id = ?");
    $stmt->bind_param("i", $driverId);
    $stmt->execute();
    $editingDriver = $stmt->get_result()->fetch_assoc();
}

$drivers = $conn->query("SELECT * FROM drivers ORDER BY driver_id DESC");

require_once __DIR__ . '/includes/header.php';
?>

<h1>Manage Drivers</h1>

<?php if ($flashMessage): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($flashMessage); ?></div>
<?php endif; ?>

<div class="card">
    <h2><?php echo $editingDriver ? 'Update Driver' : 'Add New Driver'; ?></h2>
    <form method="POST" action="admin_drivers.php">
        <input type="hidden" name="action" value="<?php echo $editingDriver ? 'update' : 'add'; ?>">
        <?php if ($editingDriver): ?>
            <input type="hidden" name="driver_id" value="<?php echo $editingDriver['driver_id']; ?>">
        <?php endif; ?>

        <label>Full Name</label>
        <input type="text" name="full_name" required value="<?php echo htmlspecialchars($editingDriver['full_name'] ?? ''); ?>">

        <label>Phone</label>
        <input type="text" name="phone_num" required value="<?php echo htmlspecialchars($editingDriver['phone_num'] ?? ''); ?>">

        <label>Availability</label>
        <select name="availability">
            <option value="available" <?php echo (isset($editingDriver) && $editingDriver['availability'] === 'available') ? 'selected' : ''; ?>>Available</option>
            <option value="unavailable" <?php echo (isset($editingDriver) && $editingDriver['availability'] === 'unavailable') ? 'selected' : ''; ?>>Unavailable</option>
        </select>

        <button type="submit"><?php echo $editingDriver ? 'Save Changes' : 'Add Driver'; ?></button>
    </form>
    <?php if ($editingDriver): ?>
        <p style="margin-top:1rem;"><a href="admin_drivers.php">Cancel edit</a></p>
    <?php endif; ?>
</div>

<div class="card">
    <h2>All Drivers</h2>
    <table>
        <tr><th>ID</th><th>Name</th><th>Phone</th><th>Availability</th><th>Actions</th></tr>
        <?php while ($row = $drivers->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['driver_id']; ?></td>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['phone_num']); ?></td>
                <td><?php echo htmlspecialchars($row['availability']); ?></td>
                <td>
                    <a href="admin_drivers.php?edit=<?php echo $row['driver_id']; ?>" class="btn" style="padding:6px 14px;">Edit</a>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this driver?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="driver_id" value="<?php echo $row['driver_id']; ?>">
                        <button type="submit" class="btn-danger" style="padding:6px 14px;">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
