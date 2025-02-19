<?php
// edit.php

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

// Fetch categories from the database
$categories = [];
$sql = "SELECT id, name FROM categories";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[$row['id']] = $row['name'];
    }
}

// Handle search
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$photos = [];
$sql = "SELECT photos.id, photos.image_url, photos.name, photos.category_id, categories.name AS category_name 
        FROM photos 
        LEFT JOIN categories ON photos.category_id = categories.id 
        WHERE photos.name LIKE '%" . $conn->real_escape_string($search_query) . "%' 
        ORDER BY photos.uploaded_at DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $photos[] = $row;
    }
}

// Handle delete or update
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete'])) {
        $photo_id = intval($_POST['photo_id']);
        $stmt = $conn->prepare("DELETE FROM photos WHERE id = ?");
        $stmt->bind_param("i", $photo_id);
        if ($stmt->execute()) {
            $success = "Photo deleted successfully.";
        } else {
            $error = "Error deleting photo.";
        }
        $stmt->close();
    } elseif (isset($_POST['update'])) {
        $photo_id = intval($_POST['photo_id']);
        $new_category_id = intval($_POST['category_id']);
        $stmt = $conn->prepare("UPDATE photos SET category_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_category_id, $photo_id);
        if ($stmt->execute()) {
            $success = "Category updated successfully.";
        } else {
            $error = "Error updating category.";
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
    <title>Edit Photos | Admin Panel | SumanOnline.Com</title>
    <link rel="stylesheet" href="../css/edit.css">
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
            <a href="index.php">Edit Photo Gallery | SumanOnline.Com</a>
            <button class="navbar-toggle" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <div class="navbar-links">
            <a href="index.php">Home</a>
            <a href="upload.php">Upload</a>
            <a href="edit.php">Edit</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="edit-container">
        <h1>Edit Photos</h1>
        <?php if (!empty($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p class="success-message"><?php echo $success; ?></p>
        <?php endif; ?>

        <!-- Search Form -->
        <form method="GET" action="edit.php" class="search-form">
            <input type="text" name="search" placeholder="Search by photo name" value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>

        <!-- Photo List -->
        <div class="photo-list">
            <?php if (empty($photos)): ?>
                <p>No photos found.</p>
            <?php else: ?>
                <?php foreach ($photos as $photo): ?>
                    <div class="photo-item">
                        <img src="<?php echo $photo['image_url']; ?>" alt="<?php echo htmlspecialchars($photo['name']); ?>">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($photo['name']); ?></p>
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($photo['category_name']); ?></p>
                        <form method="POST" action="edit.php">
                            <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">
                            <label for="category_id">Change Category:</label>
                            <select id="category_id" name="category_id" required>
                                <?php foreach ($categories as $id => $name): ?>
                                    <option value="<?php echo $id; ?>" <?php echo ($id == $photo['category_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update">Update Category</button>
                            <button type="submit" name="delete" class="delete-button" onclick="return confirm('Are you sure you want to delete this photo?');">Delete Photo</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- JavaScript for Navbar Toggle -->
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