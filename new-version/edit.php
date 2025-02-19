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

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$error = "";
$success = "";

// Fetch all photos (filtered by search query if provided)
$photos = [];
$sql = "SELECT photos.id, photos.image_url, photos.name, photos.category_id, categories.name AS category_name 
        FROM photos 
        LEFT JOIN categories ON photos.category_id = categories.id";
if (!empty($search_query)) {
    $sql .= " WHERE photos.name LIKE '%" . $conn->real_escape_string($search_query) . "%'";
}
$sql .= " ORDER BY photos.uploaded_at DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $photos[] = $row;
    }
}

// Fetch categories
$categories = [];
$sql = "SELECT id, name FROM categories";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[$row['id']] = $row['name'];
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete'])) {
        // Delete the photo
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
        // Update the photo category
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
    <title>Edit Photos</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <h1>Edit Photos</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="GET" action="edit.php">
        <label for="search">Search by Name:</label>
        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit">Search</button>
    </form>

    <div class="photo-list">
        <?php if (empty($photos)): ?>
            <p>No photos found.</p>
        <?php else: ?>
            <?php foreach ($photos as $photo): ?>
                <div class="photo-item">
                    <img src="<?php echo $photo['image_url']; ?>" alt="<?php echo htmlspecialchars($photo['name']); ?>" style="max-width: 100px; height: auto;">
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
                        <br>
                        <button type="submit" name="update">Update Category</button>
                        <button type="submit" name="delete" style="background-color: #ff4444;" onclick="return confirm('Are you sure you want to delete this photo?');">Delete Photo</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <br>
    <a href="index.php">Back to Gallery</a>
</body>

</html>