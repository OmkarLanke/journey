<?php
     $servername = "localhost";
     $username = "root";
     $password = "";
     $dbname = "journey";

     try {
         $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
         $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     } catch(PDOException $e) {
         die("Connection failed: " . $e->getMessage());
     }

     if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["photo"]) && isset($_POST["description"])) {
         $target_dir = "uploads/";
         if (!file_exists($target_dir)) {
             mkdir($target_dir, 0755, true);
         }
         $target_file = $target_dir . basename($_FILES["photo"]["name"]);
         $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
         $allowed_types = ["jpg", "jpeg", "png", "gif"];
         
         if (in_array($imageFileType, $allowed_types) && $_FILES["photo"]["size"] <= 5000000) {
             if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                 $description = htmlspecialchars($_POST["description"]);
                 $stmt = $conn->prepare("INSERT INTO memories (photo_path, description) VALUES (:photo_path, :description)");
                 $stmt->bindParam(':photo_path', $target_file);
                 $stmt->bindParam(':description', $description);
                 $stmt->execute();
                 echo json_encode(["status" => "success", "message" => "Memory added successfully!"]);
             } else {
                 echo json_encode(["status" => "error", "message" => "Error uploading photo."]);
             }
         } else {
             echo json_encode(["status" => "error", "message" => "Invalid file type or size too large."]);
         }
     } else {
         echo json_encode(["status" => "error", "message" => "Invalid request."]);
     }

     $conn = null;
     ?>