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

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role']; // Get user role from session

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
$sql = "SELECT photos.id, photos.image_url, photos.name, photos.category_id, photos.user_id, categories.name AS category_name 
        FROM photos 
        LEFT JOIN categories ON photos.category_id = categories.id 
        WHERE photos.name LIKE '%" . $conn->real_escape_string($search_query) . "%'";

// Restrict photos based on role
if ($user_role === 'user') {
    $sql .= " AND photos.user_id = $user_id"; // Only show user's photos
}

$sql .= " ORDER BY photos.uploaded_at DESC";
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
    if (isset($_POST['delete_selected'])) {
        // Delete selected photos
        if (!empty($_POST['selected_photos'])) {
            $selected_photos = $_POST['selected_photos'];
            $placeholders = implode(',', array_fill(0, count($selected_photos), '?'));
            $stmt = $conn->prepare("DELETE FROM photos WHERE id IN ($placeholders)");
            $stmt->bind_param(str_repeat('i', count($selected_photos)), ...$selected_photos);
            if ($stmt->execute()) {
                $success = "Selected photos deleted successfully.";
            } else {
                $error = "Error deleting selected photos.";
            }
            $stmt->close();
        } else {
            $error = "No photos selected.";
        }
    } elseif (isset($_POST['delete_photo'])) {
        // Delete a single photo
        $photo_id = intval($_POST['photo_id']);

        // Fetch photo details to verify ownership
        $stmt = $conn->prepare("SELECT user_id FROM photos WHERE id = ?");
        $stmt->bind_param("i", $photo_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($photo_user_id);
        $stmt->fetch();
        $stmt->close();

        // Check if user is authorized
        if ($user_role === 'admin' || ($user_role === 'user' && $photo_user_id === $user_id)) {
            $stmt = $conn->prepare("DELETE FROM photos WHERE id = ?");
            $stmt->bind_param("i", $photo_id);
            if ($stmt->execute()) {
                $success = "Photo deleted successfully.";
            } else {
                $error = "Error deleting photo.";
            }
            $stmt->close();
        } else {
            $error = "You are not authorized to perform this action.";
        }
    } elseif (isset($_POST['update'])) {
        // Update the photo category
        $photo_id = intval($_POST['photo_id']);
        $new_category_id = intval($_POST['category_id']);

        // Fetch photo details to verify ownership
        $stmt = $conn->prepare("SELECT user_id FROM photos WHERE id = ?");
        $stmt->bind_param("i", $photo_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($photo_user_id);
        $stmt->fetch();
        $stmt->close();

        // Check if user is authorized
        if ($user_role === 'admin' || ($user_role === 'user' && $photo_user_id === $user_id)) {
            $stmt = $conn->prepare("UPDATE photos SET category_id = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_category_id, $photo_id);
            if ($stmt->execute()) {
                $success = "Category updated successfully.";
            } else {
                $error = "Error updating category.";
            }
            $stmt->close();
        } else {
            $error = "You are not authorized to perform this action.";
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
    <title>Edit Photos</title>
    <link rel="stylesheet" href="./css/edit3.css">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        function toggleSelectAll() {
            const checkboxes = document.querySelectorAll('.photo-checkbox');
            const selectAll = document.getElementById('select-all');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        }
    </script>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="index.php">Photo Gallery</a>
            <button class="navbar-toggle" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <div class="navbar-links">
            <a href="index.php">Home</a>
            <a href="upload.php">Upload</a>
            <?php if ($user_role === 'admin'): ?>
                <a href="edit.php">Edit</a>
            <?php endif; ?>
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

        <!-- Delete Selected Form -->
        <form method="POST" action="edit1.php" onsubmit="return confirm('Are you sure you want to delete the selected photos?');">
            <div class="form-group">
                <label>
                    <input type="checkbox" id="select-all" onchange="toggleSelectAll()"> Select All
                </label>
                <button type="submit" name="delete_selected" class="delete-button">Delete Selected</button>
            </div>

            <!-- Photo List -->
            <div class="photo-list">
                <?php if (empty($photos)): ?>
                    <p>No photos found.</p>
                <?php else: ?>
                    <?php foreach ($photos as $photo): ?>
                        <div class="photo-item">
                            <img src="<?php echo $photo['image_url']; ?>" alt="<?php echo htmlspecialchars($photo['name']); ?>">
                            <div class="photo-actions">
                                <input type="checkbox" class="photo-checkbox" name="selected_photos[]" value="<?php echo $photo['id']; ?>">
                                <form method="POST" action="edit.php" onsubmit="return confirm('Are you sure you want to delete this photo?');">
                                    <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">
                                </form>
                            </div>
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
                                <button type="submit" name="delete_photo" class="delete-button">Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </form>
    </div>
</body>

</html>