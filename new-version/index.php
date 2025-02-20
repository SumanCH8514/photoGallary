<?php
session_start();
require "./admin/db.php";
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

// Fetch photos based on category
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
$sql = "SELECT photos.id, photos.image_url, photos.name, categories.name AS category_name 
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
    <title>Photo Gallery App | SumanOnline.Com</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/style2.css">
    <link rel="apple-touch-icon" sizes="57x57" href="fav/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="fav/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="fav/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="fav/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="fav/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="fav/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="fav/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="fav/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="fav/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="fav/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="fav/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="fav/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="fav/favicon-16x16.png">
    <link rel="manifest" href="fav/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="fav/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="./index.php">SumanPhotoGallery</a>
            <button class="navbar-toggle" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <div class="navbar-links">
            <h3 style="margin: 0; color: white;">
                <?php
                if (isset($_SESSION['username'])) {
                    echo "❤️‍🩹Welcome, " . $_SESSION['username'];
                }

                ?></h3>
            <a href="index.php">🏡Home</a>
            <a href="upload.php">🏞️Upload Photos</a>
            <a href="upload.php">⚙️Admin Panel</a>
            <form method="GET" action="upload.php" class="category-filter">
                <label for="category_id">Filter by Category:</label>
                <select id="category_id" name="category_id" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo ($category_id == $id) ? 'selected' : ''; ?>>
                            <?php echo $name; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <?php if (isset($_SESSION['username'])) {
                echo "<a href='./admin/logout.php'>❌Logout</a>";
            } else {
                echo "<a href='./admin/login.php'>◀️Login</a>";
            }
            ?>
        </div>
    </nav>

    <h1 id="main">Photo Gallery</h1>

    <!-- . "admin/"  this if if uploads folder changes-->
    <!-- grid Layout -->
    <div class="masonry-gallery">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="masonry-item">
                        <p style="text-align: center; id="chata"><b>Category:</b> ' . $row['category_name'] . '</p>
                         <img src="' . $row['image_url'] . '" alt="' . $row['name'] . '">
                        <a href="' . $row['image_url'] . '" download class="download-button"><b>Download Image</b></a>
                      </div>';
            }
        } else {
            echo '<p style="text-align: center;">No images found.</p>';
        }
        ?>
    </div>
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
    <script>
        document.querySelector('.navbar-toggle').addEventListener('click', function() {
            document.querySelector('.navbar-links').classList.toggle('active');
        });
    </script>
</body>

</html>