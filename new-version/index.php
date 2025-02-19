<?php
require "db.php";
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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

// Fetch photos based on category
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
$sql = "SELECT photos.id, photos.image_url, categories.name AS category_name 
        FROM photos 
        LEFT JOIN categories ON photos.category_id = categories.id";
if ($category_id) {
    $sql .= " WHERE photos.category_id = $category_id";
}
$sql .= " ORDER BY photos.uploaded_at DESC";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Gallery Project - SumanOnline.com</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <h1>Suman Photo Gallery</h1>
    <div class="category-filter">
        <a href="upload.php" style="text-decoration: none;"><b>Upload More Photos</b></a>
        <label for="category">Filter by Category:</label>
        <select id="category" onchange="window.location.href = 'index.php?category_id=' + this.value">
            <option value="">All Categories</option>
            <?php foreach ($categories as $id => $name): ?>
                <option value="<?php echo $id; ?>" <?php echo ($category_id == $id) ? 'selected' : ''; ?>>
                    <?php echo $name; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="masonry-gallery">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="masonry-item">
                        <img src="' . $row['image_url'] . '" alt="Gallery Image">
                        <p>Category: ' . $row['category_name'] . '</p>
                        <a href="' . $row['image_url'] . '" download class="download-button"><b>Download Image</b></a>
                      </div>';
            }
        } else {
            echo "No images found.";
        }
        ?>
    </div>
</body>

</html>