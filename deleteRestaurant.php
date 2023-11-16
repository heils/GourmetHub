<?php
require_once 'db_connect.php';
session_start();

// Check if the user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to the index page or show an access denied message
    header("Location: index.php");
    exit();
}

// Check if an ID is provided in the URL
if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $id = trim($_GET['id']);

    // Prepare a DELETE statement
    $sql = "DELETE FROM Restaurants WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Redirect to the index page after deletion
            header("Location: index.php");
            exit();
        } else {
            echo "Error deleting the restaurant. Please try again later.";
        }

        $stmt->close();
    }
} else {
    echo "Invalid request. Please try again.";
}

$conn->close();
?>
