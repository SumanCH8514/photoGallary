<?php
// upload.php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require "db.php";
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch categories from the database
$categories = [];
$sql = "SELECT id, name FROM categories";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[$row['id']] = $row['name'];
    }
}

$upload_error = "";
$upload_success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $upload_method = $_POST['upload_method'];
    $category_id = intval($_POST['category_id']); // Ensure category_id is an integer

    if ($upload_method == "file_upload") {
        // Handle multiple file uploads
        if (isset($_FILES['image_files']) && !empty($_FILES['image_files']['name'][0])) {
            $uploaded_files = $_FILES['image_files'];
            $total_files = count($uploaded_files['name']);

            for ($i = 0; $i < $total_files; $i++) {
                if ($uploaded_files['error'][$i] == 0) {
                    $target_dir = "uploads/";
                    $target_file = $target_dir . basename($uploaded_files['name'][$i]);
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                    // Check if the file is an image
                    $check = getimagesize($uploaded_files['tmp_name'][$i]);
                    if ($check !== false) {
                        // Move the file to the uploads directory
                        if (move_uploaded_file($uploaded_files['tmp_name'][$i], $target_file)) {
                            $image_url = $target_file;
                            $photo_name = $_POST['photo_names'][$i]; // Get the photo name

                            // Insert the image URL, name, and category into the database
                            $stmt = $conn->prepare("INSERT INTO photos (image_url, name, category_id) VALUES (?, ?, ?)");
                            $stmt->bind_param("ssi", $image_url, $photo_name, $category_id);

                            if ($stmt->execute()) {
                                $upload_success = "All images uploaded successfully.";
                            } else {
                                $upload_error = "Error uploading one or more images.";
                            }

                            $stmt->close();
                        } else {
                            $upload_error = "Error moving file to uploads directory.";
                        }
                    } else {
                        $upload_error = "File is not an image.";
                    }
                } else {
                    $upload_error = "Error uploading one or more files.";
                }
            }
        } else {
            $upload_error = "No files uploaded or there was an error.";
        }
    } elseif ($upload_method == "image_url") {
        // Handle image URL
        $image_url = $_POST['image_url'];
        $photo_name = $_POST['photo_name']; // Get the photo name
        if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
            $upload_error = "Invalid URL.";
        } else {
            // Insert the image URL, name, and category into the database
            $stmt = $conn->prepare("INSERT INTO photos (image_url, name, category_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $image_url, $photo_name, $category_id);

            if ($stmt->execute()) {
                $upload_success = "Image uploaded successfully.";
            } else {
                $upload_error = "Error uploading image.";
            }

            $stmt->close();
        }
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
            <label for="image_files">Choose Image Files:</label>
            <input type="file" id="image_files" name="image_files[]" accept="image/*" multiple>
            <br>
            <label for="photo_names">Photo Names (comma-separated):</label>
            <input type="text" id="photo_names" name="photo_names[]" placeholder="Enter names for each photo">
        </div>

        <div id="image-url" style="display: none;">
            <label for="image_url">Image URL:</label>
            <input type="text" id="image_url" name="image_url">
            <br>
            <label for="photo_name">Photo Name:</label>
            <input type="text" id="photo_name" name="photo_name">
        </div>

        <br>
        <label for="category_id">Category:</label>
        <select id="category_id" name="category_id" required>
            <?php foreach ($categories as $id => $name): ?>
                <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
            <?php endforeach; ?>
        </select>

        <br><br>
        <button type="submit">Upload</button>
    </form>
    <a href="index.php">View Gallery</a>
    <br>
    <a href="logout.php">Logout</a>
</body>

</html>