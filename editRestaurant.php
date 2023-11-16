<?php
require_once 'db_connect.php';
session_start();

// Check if the user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to the index page or show an access denied message
    header("Location: index.php");
    exit();
}



$name = $location = $cuisine_type = $image_url = "";
$id = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    if (isset($_POST["id"])) {
        $id = $_POST["id"];


        $name = trim($_POST['name']);
        $location = trim($_POST['location']);
        $cuisine_type = trim($_POST['cuisine_type']);


        if (isset($_FILES["restaurant_image"]) && $_FILES["restaurant_image"]["error"] == 0) {
            $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
            $filename = $_FILES["restaurant_image"]["name"];
            $filetype = $_FILES["restaurant_image"]["type"];
            $filesize = $_FILES["restaurant_image"]["size"];

            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (!array_key_exists($ext, $allowed)) die("Error: Please select a valid file format.");

            $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) die("Error: File size is larger than the allowed limit.");
        if (in_array($filetype, $allowed)) {

            if (file_exists("GourmetHub/resources/restaurantImages/" . $filename)) {
                echo $filename . " already exists.";
            } else {
                move_uploaded_file($_FILES["restaurant_image"]["tmp_name"], "GourmetHub/resources/restaurantImages/" . $filename);
                echo "Your file was uploaded successfully.";
                $image_url = "/GourmetHub/resources/restaurantImages/" . $filename;
            }
        } else {
            echo "Error: There was a problem uploading your file. Please try again.";
        }
        } else {
            $image_url = $_POST['existing_image_url'];
        }


        if (!empty($name) && !empty($location) && !empty($cuisine_type)) {

            $sql = "UPDATE Restaurants SET name = ?, location = ?, cuisine_type = ?, image_url = ? WHERE id = ?";

            if ($stmt = $conn->prepare($sql)) {

                $stmt->bind_param("ssssi", $name, $location, $cuisine_type, $image_url, $id);

                if ($stmt->execute()) {

                    header("location: index.php");
                    exit();
                } else {
                    echo "Something went wrong. Please try again later.";
                }
                $stmt->close();
            }
        }

        $conn->close();
    }
} else if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {

    $id =  trim($_GET["id"]);

    $sql = "SELECT * FROM Restaurants WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $name = $row["name"];
                $location = $row["location"];
                $cuisine_type = $row["cuisine_type"];
                $image_url = $row["image_url"];
            } else {
                header("location: error.php");
                exit();
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Restaurant</title>
</head>
<body>
    <h2>Edit Restaurant</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="existing_image_url" value="<?php echo $image_url; ?>">
        <div>
            <label>Name</label>
            <input type="text" name="name" value="<?php echo $name; ?>" required>
        </div>
        <div>
            <label>Location</label>
            <input type="text" name="location" value="<?php echo $location; ?>" required>
        </div>
        <div>
            <label>Cuisine Type</label>
            <input type="text" name="cuisine_type" value="<?php echo $cuisine_type; ?>" required>
        </div>
        <div>
            <label>Current Image</label>
            <img src="<?php echo $image_url; ?>" width="100px" alt="Restaurant Image">
            <label>Upload New Image</label>
            <input type="file" name="restaurant_image">
        </div>
        <div>
            <input type="submit" value="Submit">
        </div>
        <button type="submit" formaction="deleteRestaurant.php?id=<?php echo $id; ?>" formmethod="post" onclick="return confirm('Are you sure you want to delete this restaurant?');">Delete Restaurant</button>
    </form>
</body>
</html>
