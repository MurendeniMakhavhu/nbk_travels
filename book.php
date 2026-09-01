<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}
require_once "config/db.php";

$stmt = $conn->prepare("SELECT cust_id FROM customers WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="card"><p>Customer profile not found.</p></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
$customer = $result->fetch_assoc();
$custId = $customer['cust_id'];

$flashMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup = $_POST['pickup'];
    $dropoff = $_POST['dropoff'];
    $bookingDate = $_POST['booking_date'];
    $bookingTime = $_POST['booking_time'];
    $pax = $_POST['pax'];
    $fare = $_POST['fare'];

    $insertStmt = $conn->prepare("INSERT INTO bookings (cust_id, pickup, dropoff, booking_date, booking_time, pax, fare, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
    $insertStmt->bind_param("issssid", $custId, $pickup, $dropoff, $bookingDate, $bookingTime, $pax, $fare);
    $insertStmt->execute();

    $flashMessage = "Booking submitted! Status: pending.";
}

$bookingsStmt = $conn->prepare("SELECT * FROM bookings WHERE cust_id = ? ORDER BY booking_date DESC, booking_time DESC");
$bookingsStmt->bind_param("i", $custId);
$bookingsStmt->execute();
$bookings = $bookingsStmt->get_result();

require_once __DIR__ . '/includes/header.php';
?>

<h1>Book a Shuttle</h1>

<?php if ($flashMessage): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($flashMessage); ?></div>
<?php endif; ?>

<div class="card">
    <h2>New Booking</h2>
    <form method="POST" action="book.php">
        <label for="pickup">Pickup location</label>
        <input type="text" id="pickup" name="pickup" required placeholder="e.g. Sandton City">

        <label for="dropoff">Dropoff location</label>
        <input type="text" id="dropoff" name="dropoff" required placeholder="e.g. OR Tambo">

        <label for="booking_date">Date</label>
        <input type="date" id="booking_date" name="booking_date" required>

        <label for="booking_time">Time</label>
        <input type="time" id="booking_time" name="booking_time" required>

        <label for="pax">Passengers</label>
        <input type="number" id="pax" name="pax" min="1" max="50" value="1" required>

        <label for="fare">Fare (ZAR)</label>
        <input type="number" id="fare" name="fare" step="0.01" min="0" required placeholder="e.g. 450">

        <button type="submit">Create Booking</button>
    </form>
</div>

<div class="card">
    <h2>My Bookings</h2>
    <table>
        <tr><th>Pickup</th><th>Dropoff</th><th>Date</th><th>Time</th><th>Pax</th><th>Fare</th><th>Status</th></tr>
        <?php while ($row = $bookings->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['pickup']); ?></td>
                <td><?php echo htmlspecialchars($row['dropoff']); ?></td>
                <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
                <td><?php echo htmlspecialchars($row['booking_time']); ?></td>
                <td><?php echo htmlspecialchars($row['pax']); ?></td>
                <td>R<?php echo htmlspecialchars($row['fare']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
