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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

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
            if (file_exists("/GourmetHub/resources/restaurantImages/" . $filename)) {
                echo $filename . " is already exists.";
            } else {
                move_uploaded_file($_FILES["restaurant_image"]["tmp_name"], "/GourmetHub/resources/restaurantImages/" . $filename);
                echo "Your file was uploaded successfully.";
                $image_url = "/GourmetHub/resources/restaurantImages/" . $filename;
            }
        } else {
            echo "Error: There was a problem uploading your file. Please try again."; 
        }
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/GourmetHub/resources/restaurantImages/' . $filename;

        if (file_exists($uploadPath)) {
            echo $filename . " is already exists.";
        } else {
            if(move_uploaded_file($_FILES["restaurant_image"]["tmp_name"], $uploadPath)) {
                echo "Your file was uploaded successfully.";
                $image_url = '/GourmetHub/resources/restaurantImages/' . $filename;
            } else {
                echo "File could not be moved to the upload directory.";
            }
        }
        }  else {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $image_url = "/GourmetHub/resources/NoImage.png";
            }
        }

    if (!empty($name) && !empty($location) && !empty($cuisine_type) && !empty($image_url)) {

        $sql = "INSERT INTO Restaurants (name, location, cuisine_type, image_url) VALUES (?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {

            $stmt->bind_param("ssss", $name, $location, $cuisine_type, $image_url);

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Restaurant</title>
</head>
<body>
    <h2>Add a New Restaurant</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <div>
            <label>Name</label>
            <input type="text" name="name" value="<?php echo $name; ?>">
        </div>
        <div>
            <label>Location</label>
            <input type="text" name="location" value="<?php echo $location; ?>">
        </div>
        <div>
            <label>Cuisine Type</label>
            <input type="text" name="cuisine_type" value="<?php echo $cuisine_type; ?>">
        </div>
        <div>
            <label>Upload Image</label>
            <input type="file" name="restaurant_image">
        </div>
        <div>
            <input type="submit" value="Submit">
        </div>
    </form>
</body>
</html>
