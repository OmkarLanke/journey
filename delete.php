<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "journey";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Connection failed: " . $e->getMessage(), 3, "C:/xampp/htdocs/journey/delete_error.log");
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $e->getMessage()]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $id = intval($_POST["id"]);
    try {
        // Fetch the photo path to delete the file
        $stmt = $conn->prepare("SELECT photo_path FROM memories WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $memory = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($memory) {
            // Delete the file from uploads folder
            if (file_exists($memory['photo_path'])) {
                if (!unlink($memory['photo_path'])) {
                    error_log("Failed to delete file: " . $memory['photo_path'], 3, "C:/xampp/htdocs/journey/delete_error.log");
                    echo json_encode(["status" => "error", "message" => "Failed to delete photo file."]);
                    exit;
                }
            }
            // Delete the record from the database
            $stmt = $conn->prepare("DELETE FROM memories WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            echo json_encode(["status" => "success", "message" => "Memory deleted successfully!"]);
        } else {
            error_log("Memory not found: ID=$id", 3, "C:/xampp/htdocs/journey/delete_error.log");
            echo json_encode(["status" => "error", "message" => "Memory not found."]);
        }
    } catch(PDOException $e) {
        error_log("Database delete failed: " . $e->getMessage(), 3, "C:/xampp/htdocs/journey/delete_error.log");
        echo json_encode(["status" => "error", "message" => "Database delete failed: " . $e->getMessage()]);
    }
} else {
    error_log("Invalid request: POST=" . $_SERVER["REQUEST_METHOD"] . ", id=" . (isset($_POST["id"]) ? $_POST["id"] : "not set"), 3, "C:/xampp/htdocs/journey/delete_error.log");
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}

$conn = null;
?>