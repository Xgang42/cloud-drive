<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    $user_id = $_SESSION["user_id"];
    $file = $_FILES["file"];

    $originalName = $file["name"];
    $tmpName = $file["tmp_name"];
    $fileSize = $file["size"];
    $error = $file["error"];

    // Taille max : 5MB
    $maxSize = 5 * 1024 * 1024;

    // Extensions autorisées
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'zip'];

    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if ($error !== 0) {
        header("Location: dashboard.php?error=upload");
        exit();
    }

    if ($fileSize > $maxSize) {
        header("Location: dashboard.php?error=size");
        exit();
    }

    if (!in_array($extension, $allowedExtensions)) {
        header("Location: dashboard.php?error=type");
        exit();
    }

    $storedName = uniqid() . "." . $extension;
    $uploadPath = "uploads/" . $storedName;

    if (move_uploaded_file($tmpName, $uploadPath)) {
        $stmt = $pdo->prepare("INSERT INTO files (user_id, original_name, stored_name, file_size) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $originalName, $storedName, $fileSize]);

        header("Location: dashboard.php?success=1");
        exit();
    }
}

header("Location: dashboard.php?error=upload");
exit();
?>