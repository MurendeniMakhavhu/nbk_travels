<<?php

session_start();

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit;
}

require_once "config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare(
        "SELECT * FROM users WHERE email = ? AND role = 'admin'"
    );

    $stmt->bind_param("s", $email);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {

        echo "Admin account not found.";

    } else {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password_hash'])) {

            if ($user['status'] === 'verified') {

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];

                header("Location: admin_dashboard.php");
                exit;

            } else {

                echo "Your admin account has not been verified.";

            }

        } else {

            echo "Incorrect password.";

        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
</head>

<body>

    <h1>Admin Login</h1>

    <form method="POST" action="admin_login.php">

        <label for="email">Email:</label>
        <input
            type="email"
            id="email"
            name="email"
            required
        >

        <br><br>

        <label for="password">Password:</label>
        <input
            type="password"
            id="password"
            name="password"
            minlength="8"
            required
        >

        <br><br>

        <button type="submit">Admin Login</button>

    </form>

</body>
</html>