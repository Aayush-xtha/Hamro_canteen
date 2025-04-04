<?php
require_once('../database/db_connection.php');
require_once('../global.php');

// Set JSON response header
header('Content-Type: application/json');

// Get the user_id and branch_id from the query string
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$branch_id = isset($_GET['branch_id']) ? $_GET['branch_id'] : null;

// Validate input
if ($user_id && $branch_id) {
    // Prepare and execute the query safely
    $stmt = $conn->prepare("SELECT id, message, created_at FROM notifications WHERE user_id = ? AND branch_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("ii", $user_id, $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }

    if (!empty($notifications)) {
        echo json_encode([
            "status" => "success",
            "message" => "Notifications retrieved successfully",
            "data" => $notifications
        ]);
    } else {
        echo json_encode([
            "status" => "empty",
            "message" => "No notifications found",
            "data" => []
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        "status" => "error",
        "message" => "User ID and Branch ID are required",
        "data" => []
    ]);
}
?>
