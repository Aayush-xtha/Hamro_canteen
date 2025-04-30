<?php
require_once('../database/db_connection.php');
require_once('../global.php');

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

   
    $sql = "SELECT u.*, b.branch_name 
            FROM users u
            LEFT JOIN branches b ON u.branch_id = b.id
            WHERE u.email = '$email' AND (u.role = 'user' OR u.role = 'staff')";
    $result = mysqli_query($conn, $sql);
    $userData = mysqli_fetch_assoc($result);

    if ($userData) {
        // Verify password
        if (password_verify($password, $userData['password'])) {
            $token = bin2hex(random_bytes(32));
            $date = date("Y-m-d H:i:s");

            // Store token in tokens table
            $insertTokenSql = "INSERT INTO tokens (user_id, token, created_at, updated_at) 
                               VALUES ('{$userData['id']}', '$token', '$date', '$date')";
            mysqli_query($conn, $insertTokenSql);

            // Set image path
            $userData['image'] = !empty($userData['image']) ? $image_base . $userData['image'] : $image_base;

            echo json_encode([
                "status" => "success",
                "message" => ucfirst($userData['role']) . " login successful",
                "data" => [
                    "id" => $userData['id'],
                    "first_name" => $userData['first_name'],
                    "last_name" => $userData['last_name'],
                    "email" => $userData['email'],
                    "phone_number" => $userData['phone_number'],
                    "gender" => $userData['gender'],
                    "role" => $userData['role'],
                    "branch_id" => $userData['branch_id'],
                    "branch_name" => $userData['branch_name'],
                    "image" => $userData['image'],
                    "token" => $token
                ]
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid password"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "User not found or not authorized"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Fill in all required fields"]);
}
?>
