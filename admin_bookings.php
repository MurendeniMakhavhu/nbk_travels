<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}
require_once "config/db.php";

$flashMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'assign') {
    $bookingId = $_POST['booking_id'];
    $driverId = $_POST['driver_id'];
    $vehicleId = $_POST['vehicle_id'];

    $stmt = $conn->prepare("UPDATE bookings SET driver_id = ?, vehicle_id = ?, status = 'confirmed' WHERE booking_id = ?");
    $stmt->bind_param("iii", $driverId, $vehicleId, $bookingId);
    $stmt->execute();

    $bStmt = $conn->prepare("SELECT booking_date, booking_time FROM bookings WHERE booking_id = ?");
    $bStmt->bind_param("i", $bookingId);
    $bStmt->execute();
    $bookingRow = $bStmt->get_result()->fetch_assoc();

    $startDateTime = $bookingRow['booking_date'] . ' ' . $bookingRow['booking_time'];
    $endDateTime = date('Y-m-d H:i:s', strtotime($startDateTime . ' +2 hours'));

    $schedStmt = $conn->prepare("INSERT INTO schedules (booking_id, driver_id, vehicle_id, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, 'scheduled')");
    $schedStmt->bind_param("iiiss", $bookingId, $driverId, $vehicleId, $startDateTime, $endDateTime);
    $schedStmt->execute();

    $flashMessage = "Driver and vehicle assigned. Booking confirmed and scheduled.";
}

$bookings = $conn->query(
    "SELECT bookings.*, customers.full_name AS customer_name,
            drivers.full_name AS driver_name, vehicles.make AS vehicle_make, vehicles.model AS vehicle_model
     FROM bookings
     JOIN customers ON bookings.cust_id = customers.cust_id
     LEFT JOIN drivers ON bookings.driver_id = drivers.driver_id
     LEFT JOIN vehicles ON bookings.vehicle_id = vehicles.vehicle_id
     ORDER BY bookings.booking_date DESC, bookings.booking_time DESC"
);

$drivers = $conn->query("SELECT * FROM drivers WHERE availability = 'available'");
$vehicles = $conn->query("SELECT * FROM vehicles WHERE availability = 'available'");

require_once __DIR__ . '/includes/header.php';
?>

<h1>All Bookings</h1>

<?php if ($flashMessage): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($flashMessage); ?></div>
<?php endif; ?>

<div class="card">
    <table>
        <tr>
            <th>ID</th><th>Customer</th><th>Pickup</th><th>Dropoff</th><th>Date</th><th>Time</th>
            <th>Pax</th><th>Fare</th><th>Driver</th><th>Vehicle</th><th>Status</th><th>Assign</th>
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
                            <select name="driver_id" required style="width:auto; display:inline-block; margin-bottom:6px;">
                                <option value="">Driver...</option>
                                <?php $drivers->data_seek(0); while ($d = $drivers->fetch_assoc()): ?>
                                    <option value="<?php echo $d['driver_id']; ?>"><?php echo htmlspecialchars($d['full_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                            <select name="vehicle_id" required style="width:auto; display:inline-block; margin-bottom:6px;">
                                <option value="">Vehicle...</option>
                                <?php $vehicles->data_seek(0); while ($v = $vehicles->fetch_assoc()): ?>
                                    <option value="<?php echo $v['vehicle_id']; ?>"><?php echo htmlspecialchars($v['make'] . ' ' . $v['model']); ?></option>
                                <?php endwhile; ?>
                            </select>
                            <button type="submit" style="padding:6px 14px;">Assign</button>
                        </form>
                    <?php else: ?>
                        Assigned
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
