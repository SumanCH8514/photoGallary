<?php
// Database configuration (replace with your credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "photogallary";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle image upload
if (isset($_POST["submit"])) {
    $target_dir = "uploads/"; // Directory to store uploaded images
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is a actual image or fake image
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["fileToUpload"]["size"] > 5000000) { // 5MB limit
        $uploadOk = 0;
    }

    // Allow certain file formats
    if (
        $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif"
    ) {
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            // Insert image path into database
            $sql = "INSERT INTO images (image_path) VALUES ('$target_file')";
            if ($conn->query($sql) === TRUE) {
                echo "The file " . basename($_FILES["fileToUpload"]["name"]) . " has been uploaded and added to the gallery.";
            } else {
                echo "Error inserting image path into database: " . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}


// Fetch images from database
$sql = "SELECT id, image_path FROM images ORDER BY id DESC"; // Order by ID for latest first
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>

<head>
    <title>Masonry Photo Gallery</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            grid-gap: 15px;
        }

        .gallery-item {
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: #fff;
            position: relative;
            /* For positioning download button */
        }

        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .gallery-item img {
            width: 100%;
            display: block;
            border-radius: 10px 10px 0 0;
            object-fit: cover;
            height: 250px;
            /* Adjust as needed */
        }

        .download-button {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: rgba(0, 0, 0, 0.5);
            /* Semi-transparent black */
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            opacity: 0;
            /* Initially hidden */
            transition: opacity 0.3s ease;
        }

        .gallery-item:hover .download-button {
            opacity: 1;
            /* Visible on hover */
        }

        .upload-form {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .gallery {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                grid-gap: 10px;
            }

            .gallery-item img {
                height: 200px;
            }
        }
    </style>
</head>

<body>

    <div class="container">

        <h1>Photo Gallery</h1>

        <div class="upload-form">
            <h2>Upload Image</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                Select image to upload:
                <input type="file" name="fileToUpload" id="fileToUpload">
                <input type="submit" value="Upload Image" name="submit">
            </form>
        </div>


        <div class="gallery">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='gallery-item'>";
                    echo "<img src='" . $row["image_path"] . "' alt='Image'>";
                    echo "<a class='download-button' href='" . $row["image_path"] . "' download>Download</a>"; // Download link
                    echo "</div>";
                }
            } else {
                echo "0 results";
            }
            ?>
        </div>

    </div>

</body>

</html>

<?php
$conn->close();
?>