<?php
// upload.php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ./admin/login.php");
    exit();
}
require "./admin/db.php";
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id']; // Get user ID from session

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
    $category_id = intval($_POST['category_id']);

    if ($upload_method == "file_upload") {
        // Handle file upload
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
                            $photo_name = pathinfo($uploaded_files['name'][$i], PATHINFO_FILENAME);

                            // Insert the image URL, name, category, and user ID into the database
                            $stmt = $conn->prepare("INSERT INTO photos (image_url, name, category_id, user_id) VALUES (?, ?, ?, ?)");
                            $stmt->bind_param("ssii", $image_url, $photo_name, $category_id, $user_id);

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
        if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
            $upload_error = "Invalid URL.";
        } else {
            // Extract filename from URL
            $parsed_url = parse_url($image_url);
            $path = $parsed_url['path'];
            $photo_name = pathinfo($path, PATHINFO_FILENAME);

            // Insert the image URL, name, category, and user ID into the database
            $stmt = $conn->prepare("INSERT INTO photos (image_url, name, category_id, user_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssii", $image_url, $photo_name, $category_id, $user_id);

            if ($stmt->execute()) {
                $upload_success = "Image uploaded successfully.";
            } else {
                $upload_error = "Error uploading image.";
            }

            $stmt->close();
        }
    }
}

// Query to retrieve the username from the database
$username = mysqli_query($conn, "SELECT username FROM users WHERE id = '$user_id'");

// Fetch the username from the query result
$username = mysqli_fetch_assoc($username);
$username = $username['username'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Photo | SumanOnline.Com</title>
    <link rel="stylesheet" href="./css/admin.css">
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
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="index.php">SumanPhotoGallery | Admin</a>
            <button class="navbar-toggle" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <div class="navbar-links">
            <h3 style="margin: 0; color: white;">
                <?php
                echo "❤️‍🩹Welcome, $username!";
                ?></h3>
            <a href="./index.php">🏞️View Gallery</a>
            <a href="./edit.php">⚙️Edit Table</a>
            <a href="./admin/logout.php">❌Logout</a>
        </div>
    </nav>
    <div class="line">

    </div>

    <div class="upload-container">
        <h1>Upload Photo</h1>
        <?php if (!empty($upload_error)): ?>
            <p class="error-message"><?php echo $upload_error; ?></p>
        <?php endif; ?>
        <?php if (!empty($upload_success)): ?>
            <p class="success-message"><?php echo $upload_success; ?></p>
        <?php endif; ?>
        <form method="POST" action="upload.php" enctype="multipart/form-data">
            <div class="form-group">
                <label>
                    <input type="radio" name="upload_method" value="file_upload" checked onchange="toggleUploadMethod()"> Upload Image File
                </label>
                <label>
                    <input type="radio" name="upload_method" value="image_url" onchange="toggleUploadMethod()"> Provide Image URL
                </label>
            </div>

            <div id="file-upload" class="form-group">
                <label for="image_files">Choose Image Files:</label>
                <input type="file" id="image_files" name="image_files[]" accept="image/*" multiple>
            </div>

            <div id="image-url" class="form-group" style="display: none;">
                <label for="image_url">Image URL:</label>
                <input type="text" id="image_url" name="image_url">
            </div>

            <div class="form-group">
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required>
                    <?php foreach ($categories as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="upload-button">Upload</button>
        </form>

    </div>

    <!-- JavaScript for Toggle Upload Method -->
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
    <script>
        document.querySelector('.navbar-toggle').addEventListener('click', function() {
            document.querySelector('.navbar-links').classList.toggle('active');
        });
    </script>

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