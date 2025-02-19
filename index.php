<?php
// index.php

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

$sql = "SELECT * FROM photos ORDER BY uploaded_at DESC";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Gallery</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <h1>Photo Gallery</h1>
    <div class="masonry-gallery">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="masonry-item">
                        <img src="' . $row['image_url'] . '" alt="Gallery Image">
                        <a href="' . $row['image_url'] . '" download class="download-button">Download</a>
                      </div>';
            }
        } else {
            echo "No images found.";
        }
        ?>
    </div>
    <a href="upload.php">Upload More Photos</a>
</body>

</html>