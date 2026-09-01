<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NBK Travel</title>
    <link rel="stylesheet" href="/nbk_travels/style.css">
</head>
<body>
<header class="site-header">
    <div class="brand">
        NBK Travel
        <span>Shuttle Booking Management</span>
    </div>
    <nav class="site-nav" aria-label="Primary navigation">
        <ul>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_customers.php">Customers</a></li>
                <li><a href="admin_drivers.php">Drivers</a></li>
                <li><a href="admin_vehicles.php">Vehicles</a></li>
                <li><a href="admin_bookings.php">Bookings</a></li>
                <li><a href="admin_logout.php">Log out</a></li>
            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'customer'): ?>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="book.php">Book a Shuttle</a></li>
                <li><a href="logout.php">Log out</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
                <li><a href="admin_login.php">Admin</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main>