<?php
require_once('../database/db_connection.php');
require_once('../global.php');

// Get the user_id and branch_id from the URL query parameters
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$branch_id = isset($_GET['branch_id']) ? $_GET['branch_id'] : null;

if ($user_id && $branch_id) {
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
        "message" => "User ID and Branch ID are required"
    ]);
}
?>
