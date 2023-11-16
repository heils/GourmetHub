<?php
require_once 'db_connect.php';
session_start();

$restaurantDetails = [];
$menuItems = [];

if(isset($_GET['id']) && is_numeric($_GET['id'])) {
    $restaurant_id = $_GET['id'];

    if($stmt = $conn->prepare("SELECT name, location, cuisine_type, image_url FROM Restaurants WHERE id = ?")) {
        $stmt->bind_param("i", $restaurant_id);
        $stmt->execute();
        $stmt->store_result();

        if($stmt->num_rows == 1) {
            $stmt->bind_result($name, $location, $cuisine_type, $image_url);
            $stmt->fetch();
            $restaurantDetails = [
                'name' => $name,
                'location' => $location,
                'cuisine_type' => $cuisine_type,
                'image_url' => $image_url
            ];
        } else {
            echo "Restaurant not found.";
        }
        $stmt->close();
    }

    if($stmt = $conn->prepare("SELECT name, price, description, image_url FROM Menus WHERE restaurant_id = ?")) {
        $stmt->bind_param("i", $restaurant_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while($row = $result->fetch_assoc()) {
            $menuItems[] = $row;
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
    <link rel="stylesheet" href="restaurant.css">
    <title>Restaurant Details</title>
</head>
<body>

<?php if($restaurantDetails): ?>
    <h1><?= htmlspecialchars($restaurantDetails['name']) ?></h1>
    <p>Location: <?= htmlspecialchars($restaurantDetails['location']) ?></p>
    <p>Cuisine Type: <?= htmlspecialchars($restaurantDetails['cuisine_type']) ?></p>
    <img src="<?= htmlspecialchars($restaurantDetails['image_url']) ?>" alt="<?= htmlspecialchars($restaurantDetails['name']) ?>" style="max-width: 400px;">
    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin'): ?>
        <a href="addMenuItem.php?restaurant_id=<?= urlencode($restaurant_id) ?>" class="btn btn-primary">Add New Menu Item</a>
    <?php endif; ?>
    
    <h2>Menu Items</h2>
    <table>
        <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Description</th>
            <th>Image</th>
        </tr>
        <?php foreach($menuItems as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= htmlspecialchars($item['price']) ?></td>
            <td><?= htmlspecialchars($item['description']) ?></td>
            <td>
                <?php if($item['image_url']): ?>
                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>No restaurant details to display.</p>
<?php endif; ?>

</body>
</html>
