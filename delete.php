<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET["id"])) {
    $file_id = $_GET["id"];
    $user_id = $_SESSION["user_id"];

    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$file_id, $user_id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        $filePath = "uploads/" . $file["stored_name"];

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $deleteStmt = $pdo->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
        $deleteStmt->execute([$file_id, $user_id]);
    }
}

header("Location: dashboard.php");
exit();
?>