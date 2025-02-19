<?php
session_start(); // Start the session
require "db.php"; // Include database connection file

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Login handling
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Replace with your actual login logic (e.g., database query)
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND password = ?"); // Prepared statement
    $stmt->bind_param("ss", $username, $password); // Bind parameters
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username; // Store username in session
        header("Location: upload.php"); // Redirect to upload page
        exit(); // Important: Stop further execution
    } else {
        $error = "Incorrect username or password.";
    }
    $stmt->close();
}

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to login page or display login form
?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Login</title>
    </head>

    <body>
        <h2>Login</h2>
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            Username: <input type="text" name="username" required><br>
            Password: <input type="password" name="password" required><br>
            <input type="submit" name="login" value="Login">
        </form>
    </body>

    </html>
<?php
    exit(); // Stop further execution on the login page
}

$conn->close();
?>