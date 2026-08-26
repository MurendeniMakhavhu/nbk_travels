<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}
require_once "config/db.php";

$flashMessage = "";
$editingVehicle = null;

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $vehicleId = $_POST['vehicle_id'];
    $stmt = $conn->prepare("DELETE FROM vehicles WHERE vehicle_id = ?");
    $stmt->bind_param("i", $vehicleId);
    $stmt->execute();
    $flashMessage = "Vehicle deleted.";
}

// ADD or UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['add', 'update'])) {
    $make = $_POST['make'];
    $model = $_POST['model'];
    $regNum = $_POST['registration_num'];
    $seats = $_POST['seating_capacity'];
    $availability = $_POST['availability'];

    if ($_POST['action'] === 'add') {
        $stmt = $conn->prepare(
            "INSERT INTO vehicles (make, model, registration_num, seating_capacity, availability) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssis", $make, $model, $regNum, $seats, $availability);
        $stmt->execute();
        $flashMessage = "Vehicle added.";
    } else {
        $vehicleId = $_POST['vehicle_id'];
        $stmt = $conn->prepare(
            "UPDATE vehicles SET make = ?, model = ?, registration_num = ?, seating_capacity = ?, availability = ? WHERE vehicle_id = ?"
        );
        $stmt->bind_param("sssisi", $make, $model, $regNum, $seats, $availability, $vehicleId);
        $stmt->execute();
        $flashMessage = "Vehicle updated.";
    }
}

// Load a vehicle into the form for editing
if (isset($_GET['edit'])) {
    $vehicleId = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM vehicles WHERE vehicle_id = ?");
    $stmt->bind_param("i", $vehicleId);
    $stmt->execute();
    $editingVehicle = $stmt->get_result()->fetch_assoc();
}

$vehicles = $conn->query("SELECT * FROM vehicles ORDER BY vehicle_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vehicles</title>
</head>
<body>
    <h1>Manage Vehicles</h1>

    <?php if ($flashMessage): ?>
        <p style="color:green;"><?php echo htmlspecialchars($flashMessage); ?></p>
    <?php endif; ?>

    <h2><?php echo $editingVehicle ? 'Update Vehicle' : 'Add New Vehicle'; ?></h2>
    <form method="POST" action="admin_vehicles.php">
        <input type="hidden" name="action" value="<?php echo $editingVehicle ? 'update' : 'add'; ?>">
        <?php if ($editingVehicle): ?>
            <input type="hidden" name="vehicle_id" value="<?php echo $editingVehicle['vehicle_id']; ?>">
        <?php endif; ?>

        <label>Make:
            <input type="text" name="make" required
                   value="<?php echo htmlspecialchars($editingVehicle['make'] ?? ''); ?>">
        </label><br><br>

        <label>Model:
            <input type="text" name="model" required
                   value="<?php echo htmlspecialchars($editingVehicle['model'] ?? ''); ?>">
        </label><br><br>

        <label>Registration Number:
            <input type="text" name="registration_num" required
                   value="<?php echo htmlspecialchars($editingVehicle['registration_num'] ?? ''); ?>">
        </label><br><br>

        <label>Seating Capacity:
            <input type="number" name="seating_capacity" required min="1" max="100"
                   value="<?php echo htmlspecialchars($editingVehicle['seating_capacity'] ?? ''); ?>">
        </label><br><br>

        <label>Availability:
            <select name="availability">
                <option value="available" <?php echo (isset($editingVehicle) && $editingVehicle['availability'] === 'available') ? 'selected' : ''; ?>>Available</option>
                <option value="unavailable" <?php echo (isset($editingVehicle) && $editingVehicle['availability'] === 'unavailable') ? 'selected' : ''; ?>>Unavailable</option>
            </select>
        </label><br><br>

        <button type="submit"><?php echo $editingVehicle ? 'Save Changes' : 'Add Vehicle'; ?></button>
    </form>
    <?php if ($editingVehicle): ?>
        <p><a href="admin_vehicles.php">Cancel edit</a></p>
    <?php endif; ?>

    <h2>All Vehicles</h2>
    <table border="1">
        <tr>
            <th>ID</th><th>Make</th><th>Model</th><th>Reg No.</th><th>Seats</th><th>Availability</th><th>Actions</th>
        </tr>
        <?php while ($row = $vehicles->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['vehicle_id']; ?></td>
                <td><?php echo htmlspecialchars($row['make']); ?></td>
                <td><?php echo htmlspecialchars($row['model']); ?></td>
                <td><?php echo htmlspecialchars($row['registration_num']); ?></td>
                <td><?php echo $row['seating_capacity']; ?></td>
                <td><?php echo htmlspecialchars($row['availability']); ?></td>
                <td>
                    <a href="admin_vehicles.php?edit=<?php echo $row['vehicle_id']; ?>">Edit</a>
                    &nbsp;
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this vehicle?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="vehicle_id" value="<?php echo $row['vehicle_id']; ?>">
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