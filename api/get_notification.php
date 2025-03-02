<?php
require_once('../database/db_connection.php');
require_once('../global.php');

// Get the Authorization header
$headers = apache_request_headers();
$authorizationHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

// Get the user_id and branch_id from the URL query parameters
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$branch_id = isset($_GET['branch_id']) ? $_GET['branch_id'] : null;

if ($authorizationHeader) {
    if (preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
        $token = $matches[1];

        // Verify token and get user info
        $sql = "SELECT u.id AS user_id, u.branch_id 
                FROM users u 
                JOIN tokens t ON u.id = t.user_id 
                WHERE t.token = '$token' 
                AND t.updated_at > NOW() - INTERVAL 1 DAY";

        $result = mysqli_query($conn, $sql);
        $userData = mysqli_fetch_assoc($result);

        if ($userData) {
            // If user_id is passed and matches the user_id from token
            if ($user_id && $branch_id && $userData['user_id'] == $user_id && $userData['branch_id'] == $branch_id) {

                // Fetch notifications for the user and branch
                $notificationSql = "SELECT id, message, created_at 
                                    FROM notifications 
                                    WHERE user_id = '$user_id' AND branch_id = '$branch_id'
                                    ORDER BY created_at DESC";

                $notificationResult = mysqli_query($conn, $notificationSql);

                if (mysqli_num_rows($notificationResult) > 0) {
                    $notifications = [];
                    while ($row = mysqli_fetch_assoc($notificationResult)) {
                        $notifications[] = $row;
                    }

                    echo json_encode([
                        "status" => "success",
                        "message" => "Notifications retrieved successfully",
                        "data" => $notifications
                    ]);
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "No notifications found"
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "User ID or Branch ID mismatch or invalid data"
                ]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid or expired token"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Bearer token missing or invalid"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Authorization header missing"]);
}
?>
