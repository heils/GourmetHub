<?php
require_once 'db_connect.php';
session_start();

// Check if the user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to the index page or show an access denied message
    header("Location: index.php");
    exit();
}

define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/GourmetHub/resources/');

$restaurant_id = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : 0;
$errorMessage = "";
$image_url = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restaurant_id'])) {

    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    $restaurant_id = (int)$_POST['restaurant_id'];

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
        $filename = $_FILES["image"]["name"];
        $filetype = $_FILES["image"]["type"];
        $filesize = $_FILES["image"]["size"];

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            $errorMessage = "Error: Please select a valid file format.";
        } else {
            $maxsize = 5 * 1024 * 1024;
            if ($filesize > $maxsize) {
                $errorMessage = "Error: File size is larger than the allowed limit.";
            } else {
                $uniqueFilename = uniqid() . "_" . $filename;
                $targetPath = UPLOAD_DIR . $uniqueFilename;
                if (file_exists($targetPath)) {
                    $errorMessage = $uniqueFilename . " already exists.";
                } else {
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
                        echo "Your file was uploaded successfully.";
                        $image_url = '/GourmetHub/resources/' . $uniqueFilename;
                    } else {
                        $errorMessage = "Error: There was a problem uploading your file. Please try again.";
                    }
                }
            }
        }
    } else {
        $image_url = "/GourmetHub/resources/NoImage.png";
    }

    if (empty($errorMessage) && !empty($name) && !empty($price) && !empty($description) && !empty($image_url)) {
        $sql = "INSERT INTO Menus (name, price, description, restaurant_id, image_url) VALUES (?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {

            $stmt->bind_param("sdsss", $name, $price, $description, $restaurant_id, $image_url);

            if ($stmt->execute()) {

                header("location: index.php");
                exit();
            } else {
                $errorMessage = "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    } else {
        $errorMessage = "Please fill in all required fields and ensure the image is uploaded.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Menu Item</title>
</head>
<body>
<h2>Add Menu Item</h2>

<?php if($errorMessage): ?>
<p>Error: <?= htmlspecialchars($errorMessage) ?></p>
<?php endif; ?>

<form action="addMenuItem.php?restaurant_id=<?= htmlspecialchars($restaurant_id) ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="restaurant_id" value="<?= htmlspecialchars($restaurant_id) ?>">

    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required><br>

    <label for="price">Price:</label>
    <input type="text" id="price" name="price" required pattern="\d+(\.\d{2})?" title="Please enter a valid price."><br>

    <label for="description">Description:</label>
    <textarea id="description" name="description" required></textarea><br>

    <label for="image">Image:</label>
    <input type="file" id="image" name="image"><br>

    <input type="submit" value="Add Menu Item">
</form>

</body>
</html>
