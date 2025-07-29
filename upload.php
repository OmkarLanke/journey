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
    error_log("Connection failed: " . $e->getMessage(), 3, "C:/xampp/htdocs/journey/upload_error.log");
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $e->getMessage()]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["photo"]) && isset($_POST["title"]) && isset($_POST["description"])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $target_file = $target_dir . basename($_FILES["photo"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ["jpg", "jpeg", "png", "gif"];
    
    if (!in_array($imageFileType, $allowed_types)) {
        error_log("Invalid file type: $imageFileType", 3, "C:/xampp/htdocs/journey/upload_error.log");
        echo json_encode(["status" => "error", "message" => "Invalid file type. Use JPG, PNG, or GIF."]);
        exit;
    }
    if ($_FILES["photo"]["size"] > 5000000) {
        error_log("File too large: " . $_FILES["photo"]["size"], 3, "C:/xampp/htdocs/journey/upload_error.log");
        echo json_encode(["status" => "error", "message" => "File size too large. Max 5MB."]);
        exit;
    }
    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
        try {
            $title = htmlspecialchars($_POST["title"]);
            $description = htmlspecialchars($_POST["description"]);
            $stmt = $conn->prepare("INSERT INTO memories (photo_path, title, description) VALUES (:photo_path, :title, :description)");
            $stmt->bindParam(':photo_path', $target_file);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->execute();
            echo json_encode(["status" => "success", "message" => "Memory added successfully!"]);
        } catch(PDOException $e) {
            error_log("Database insert failed: " . $e->getMessage(), 3, "C:/xampp/htdocs/journey/upload_error.log");
            echo json_encode(["status" => "error", "message" => "Database insert failed: " . $e->getMessage()]);
        }
    } else {
        error_log("File upload failed: " . $_FILES["photo"]["error"], 3, "C:/xampp/htdocs/journey/upload_error.log");
        echo json_encode(["status" => "error", "message" => "Error uploading photo: " . $_FILES["photo"]["error"]]);
    }
} else {
    error_log("Invalid request: POST=" . $_SERVER["REQUEST_METHOD"] . ", photo=" . (isset($_FILES["photo"]) ? "set" : "not set") . ", title=" . (isset($_POST["title"]) ? "set" : "not set") . ", description=" . (isset($_POST["description"]) ? "set" : "not set"), 3, "C:/xampp/htdocs/journey/upload_error.log");
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}

$conn = null;
