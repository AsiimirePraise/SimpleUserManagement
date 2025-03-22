<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION["user_id"];
$conn = new mysqli("localhost", "root", "", "usermanagement");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user input
$name = $_POST["username"]; 
$email = $_POST["email"];
$password = $_POST["password"];

if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET username=?, email=?, password=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $email, $hashed_password, $user_id);
} else {
    $sql = "UPDATE users SET username=?, email=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $email, $user_id);
}
$stmt->execute();
$stmt->close();

if (!empty($_FILES["profile_picture"]["name"])) {
    $target_dir = "uploads/";
    $file_name = basename($_FILES["profile_picture"]["name"]);
    $file_name = time() . "_" . $file_name;
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ["jpg", "jpeg", "png", "gif"];
    
    if (in_array($imageFileType, $allowed_types)) {
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            // Delete old profile picture if not default
            $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id=?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($old_picture);
            $stmt->fetch();
            $stmt->close();
            
            if ($old_picture && $old_picture != "default.png") {
                // Make sure the file exists before attempting to delete
                if (file_exists("uploads/" . $old_picture)) {
                    unlink("uploads/" . $old_picture);
                }
            }
            $stmt = $conn->prepare("UPDATE users SET profile_picture=? WHERE id=?");
            $stmt->bind_param("si", $file_name, $user_id);
            $stmt->execute();
            $stmt->close();
        } else {
            header("Location: edit.php?error=upload_failed");
            exit();
        }
    } else {
        header("Location: edit.php?error=invalid_type");
        exit();
    }
}

$conn->close();
header("Location: edit.php?success=1");
exit();
?>