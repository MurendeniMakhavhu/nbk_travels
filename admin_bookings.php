<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}
require_once "config/db.php";

$flashMessage = "";

// Handle driver/vehicle assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'assign') {
    $bookingId = $_POST['booking_id'];
    $driverId = $_POST['driver_id'];
    $vehicleId = $_POST['vehicle_id'];

    $stmt = $conn->prepare(
        "UPDATE bookings SET driver_id = ?, vehicle_id = ?, status = 'confirmed' WHERE booking_id = ?"
    );
    $stmt->bind_param("iii", $driverId, $vehicleId, $bookingId);
    $stmt->execute();

    $flashMessage = "Driver and vehicle assigned. Booking confirmed.";
}

// All bookings, joined with customer/driver/vehicle names.
// LEFT JOIN on drivers/vehicles because a booking may not have one assigned yet.
$bookings = $conn->query(
    "SELECT bookings.*, customers.full_name AS customer_name,
            drivers.full_name AS driver_name, vehicles.make AS vehicle_make, vehicles.model AS vehicle_model
     FROM bookings
     JOIN customers ON bookings.cust_id = customers.cust_id
     LEFT JOIN drivers ON bookings.driver_id = drivers.driver_id
     LEFT JOIN vehicles ON bookings.vehicle_id = vehicles.vehicle_id
     ORDER BY bookings.booking_date DESC, bookings.booking_time DESC"
);

// Lists for the assignment dropdowns
$drivers = $conn->query("SELECT * FROM drivers WHERE availability = 'available'");
$vehicles = $conn->query("SELECT * FROM vehicles WHERE availability = 'available'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Bookings</title>
</head>
<body>
    <h1>All Bookings</h1>

    <?php if ($flashMessage): ?>
        <p style="color:green;"><?php echo htmlspecialchars($flashMessage); ?></p>
    <?php endif; ?>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Pickup</th>
            <th>Dropoff</th>
            <th>Date</th>
            <th>Time</th>
            <th>Pax</th>
            <th>Fare</th>
            <th>Driver</th>
            <th>Vehicle</th>
            <th>Status</th>
            <th>Assign</th>
        </tr>
        <?php while ($row = $bookings->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['booking_id']; ?></td>
                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($row['pickup']); ?></td>
                <td><?php echo htmlspecialchars($row['dropoff']); ?></td>
                <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
                <td><?php echo htmlspecialchars($row['booking_time']); ?></td>
                <td><?php echo $row['pax']; ?></td>
                <td>R<?php echo htmlspecialchars($row['fare']); ?></td>
                <td><?php echo $row['driver_name'] ? htmlspecialchars($row['driver_name']) : '—'; ?></td>
                <td><?php echo $row['vehicle_make'] ? htmlspecialchars($row['vehicle_make'] . ' ' . $row['vehicle_model']) : '—'; ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <?php if ($row['driver_id'] === null): ?>
                        <form method="POST" action="admin_bookings.php">
                            <input type="hidden" name="action" value="assign">
                            <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">

                            <select name="driver_id" required>
                                <option value="">Driver...</option>
                                <?php
                                $drivers->data_seek(0); // reset pointer so it can be reused each loop
                                while ($d = $drivers->fetch_assoc()):
                                ?>
                                    <option value="<?php echo $d['driver_id']; ?>">
                                        <?php echo htmlspecialchars($d['full_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <select name="vehicle_id" required>
                                <option value="">Vehicle...</option>
                                <?php
                                $vehicles->data_seek(0);
                                while ($v = $vehicles->fetch_assoc()):
                                ?>
                                    <option value="<?php echo $v['vehicle_id']; ?>">
                                        <?php echo htmlspecialchars($v['make'] . ' ' . $v['model']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <button type="submit">Assign</button>
                        </form>
                    <?php else: ?>
                        Assigned
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <p><a href="admin_dashboard.php">&larr; Back to Dashboard</a></p>
    <p><a href="admin_logout.php">Log out</a></p>
</body>
</html>