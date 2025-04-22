<?php
require_once('../database/db_connection.php');
require_once('../global.php');

// Ensure the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method."
    ]);
    exit();
}

// Check if required fields are present
if (isset($_POST['id']) && isset($_POST['old_password']) && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
    $userId = $_POST['id'];
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Check if new password matches confirm password
    if ($newPassword !== $confirmPassword) {
        echo json_encode([
            "status" => "error",
            "message" => "New password and confirm password do not match."
        ]);
        exit();
    }

    // Query to fetch user's current password from the database
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $storedPassword = $row['password'];

        // Verify old password
        if (password_verify($oldPassword, $storedPassword)) {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the user's password in the database
            $updateSql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $updateSql);
            mysqli_stmt_bind_param($stmt, "si", $hashedPassword, $userId);
            $updateResult = mysqli_stmt_execute($stmt);

            if ($updateResult) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Password updated successfully"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to update password, please try again later."
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Old password does not match."
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "User not found."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "All fields are required"
    ]);
}
?>
