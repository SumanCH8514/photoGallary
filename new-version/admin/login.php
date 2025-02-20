<?php
// login.php

session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "photo_gallery";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch user from the database
    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password, $role);

    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role; // Store role in session
        header("Location: ../index.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SumanOnline.Com</title>
    <link rel="stylesheet" href="../css/login.css">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="apple-touch-icon" sizes="57x57" href="../fav/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="../fav/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="../fav/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="../fav/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="../fav/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="../fav/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="../fav/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="../fav/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../fav/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../fav/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../fav/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="../fav/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../fav/favicon-16x16.png">
    <link rel="manifest" href="../fav/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="../fav/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
</head>

<body>
    <div class="login-container">
        <h1>Login</h1>
        <?php if (!empty($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-button">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
        <p>&copy; 2018-2025 <a href="../index.php" style="text-decoration: none;"><b>SumanPhotoGallery</b></a></p>
    </div>
    <div id="footer">
        <p style="font-size: 0.7rem; text-align: center">
            &copy; 2018-<span id="copyright"></span>
            <span id="copyright">
                <script>
                    document
                        .getElementById("copyright")
                        .appendChild(document.createTextNode(new Date().getFullYear()));
                </script>
            </span>
            <a
                href="https://sumanonline.com "
                target="_blank"
                style="text-decoration: none; font-weight: bold">SumanOnline.com</a>
            | All Rights Reserved.
        </p>

    </div>
</body>

</html>