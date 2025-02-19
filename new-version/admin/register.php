<?php
// register.php

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
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "Username already exists.";
    } else {
        // Insert new user into the database
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            $success = "Registration successful. <a href='login.php'>Login here</a>";
        } else {
            $error = "Error registering user.";
        }
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
    <title>Register | SumanOnline.Com</title>
    <link rel="stylesheet" href="../css/reg.css">
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
    <div class="register-container">
        <h1>Register</h1>
        <?php if (!empty($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p class="success-message"><?php echo $success; ?></p>
        <?php endif; ?>
        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="register-button">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
        <br>
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