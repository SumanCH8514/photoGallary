<?php
// upload.php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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

$upload_error = "";
$upload_success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $upload_method = $_POST['upload_method'];

    if ($upload_method == "file_upload") {
        // Handle file upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES['image_file']['name']);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if the file is an image
            $check = getimagesize($_FILES['image_file']['tmp_name']);
            if ($check !== false) {
                // Move the file to the uploads directory
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_file)) {
                    $image_url = $target_file;
                } else {
                    $upload_error = "Error uploading file.";
                }
            } else {
                $upload_error = "File is not an image.";
            }
        } else {
            $upload_error = "No file uploaded or there was an error.";
        }
    } elseif ($upload_method == "image_url") {
        // Handle image URL
        $image_url = $_POST['image_url'];
        if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
            $upload_error = "Invalid URL.";
        }
    }

    // If no errors, insert the image URL into the database
    if (empty($upload_error)) {
        $stmt = $conn->prepare("INSERT INTO photos (image_url) VALUES (?)");
        $stmt->bind_param("s", $image_url);

        if ($stmt->execute()) {
            $upload_success = "Image uploaded successfully.";
        } else {
            $upload_error = "Error uploading image.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Photo</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        function toggleUploadMethod() {
            const fileUpload = document.getElementById("file-upload");
            const imageUrl = document.getElementById("image-url");

            if (document.querySelector('input[name="upload_method"]:checked').value === "file_upload") {
                fileUpload.style.display = "block";
                imageUrl.style.display = "none";
            } else {
                fileUpload.style.display = "none";
                imageUrl.style.display = "block";
            }
        }
    </script>
</head>

<body>
    <h1>Upload Photo</h1>
    <?php if (!empty($upload_error)): ?>
        <p style="color: red;"><?php echo $upload_error; ?></p>
    <?php endif; ?>
    <?php if (!empty($upload_success)): ?>
        <p style="color: green;"><?php echo $upload_success; ?></p>
    <?php endif; ?>
    <form method="POST" action="upload.php" enctype="multipart/form-data">
        <label>
            <input type="radio" name="upload_method" value="file_upload" checked onchange="toggleUploadMethod()"> Upload Image File
        </label>
        <label>
            <input type="radio" name="upload_method" value="image_url" onchange="toggleUploadMethod()"> Provide Image URL
        </label>
        <br><br>

        <div id="file-upload">
            <label for="image_file">Choose Image File:</label>
            <input type="file" id="image_file" name="image_file" accept="image/*">
        </div>

        <div id="image-url" style="display: none;">
            <label for="image_url">Image URL:</label>
            <input type="text" id="image_url" name="image_url">
        </div>

        <br>
        <button type="submit">Upload</button>
    </form>
    <a href="index.php">View Gallery</a>
    <br>
    <a href="logout.php">Logout</a>
</body>

</html>