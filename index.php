<?php
session_start();

require_once 'db_connect.php';

$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

$sql = "SELECT id, name, location, cuisine_type, image_url FROM Restaurants WHERE name LIKE ? ORDER BY name";
$stmt = $conn->prepare($sql);

$searchTerm = '%' . $search . '%';
$stmt->bind_param("s", $searchTerm);

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css">
    <title>Document</title>
</head>
<body>
    <div class="header">
        <h1>Welcome to GourmetHub</h1>
    </div>

    <ul class="nav">
        <li><a href="index.php">Home</a></li>
        <li><a href="register.php">Register</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
        <?php endif; ?>
    </ul>

    <div class="clear-fix"></div>

    <form method="get" action="index.php">
        <input type="text" name="search" placeholder="Search restaurants..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>

    <?php if (isset($_SESSION['user_id'])): ?>
        <?php if ($_SESSION['role'] == 'admin'): ?>
            <a href="addRestaurant.php">
                <button type="button">Add New Restaurant</button>
            </a>
        <?php endif; ?>
    <?php endif; ?>

    <div class="grid-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="grid-item">
                    <h2>
                        <a href="restaurant.php?id=<?= urlencode($row['id']) ?>">
                            <?= htmlspecialchars($row['name']) ?>
                        </a>
                    </h2>
                    <p><?= htmlspecialchars($row['location']) ?></p>
                    <p><i><?= htmlspecialchars($row['cuisine_type']) ?></i></p>
                    <a href="restaurant.php?id=<?= urlencode($row['id']) ?>">
                        <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="Restaurant Image" class="restaurant-image">
                    </a>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin'): ?>
                        <a href="editRestaurant.php?id=<?= urlencode($row['id']) ?>">
                            <button type="button">Edit</button>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No restaurants found.</p>
        <?php endif; ?>
    </div>

<?php
$stmt->close();
$conn->close();
?>
</body>
</html>
