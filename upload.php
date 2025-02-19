<?php
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}
require "db.php"; // Include database connection file
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Image upload handling
if (isset($_POST["submit"])) {
    // ... (same upload code as before, but remove the database connection part, as it's already at the beginning of this file)
}


// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();  // Destroy the session
    header("Location: login.php"); // Redirect to the login page
    exit();
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Upload Image</title>
    <style>
        /* ... (same CSS as before) */
    </style>
</head>

<body>

    <h2>Upload Image</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        Select image to upload:
        <input type="file" name="fileToUpload" id="fileToUpload">
        <input type="submit" value="Upload Image" name="submit">
    </form>

    <a href="?logout">Logout</a>
</body>

</html>

<?php
$conn->close();
?>