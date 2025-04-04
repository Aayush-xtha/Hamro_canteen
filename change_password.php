<?php
require_once('./database/db_connection.php');
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

$branch_id = $_SESSION['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        echo json_encode(["status" => "error", "message" => "New passwords do not match."]);
        exit();
    }

    // Check current password in DB
    $stmt = $conn->prepare("SELECT password FROM branches WHERE id = ?");
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (!$hashed_password || !password_verify($current_password, $hashed_password)) {
        echo json_encode(["status" => "error", "message" => "Current password is incorrect."]);
        exit();
    }

    // Update new password
    $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    $update = $conn->prepare("UPDATE branches SET password = ? WHERE id = ?");
    $update->bind_param("si", $new_hashed_password, $branch_id);

    if ($update->execute()) {
        echo json_encode(["status" => "success", "message" => "Password changed successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update password."]);
    }

    $update->close();
    exit();
}
?>
