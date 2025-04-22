<?php
require_once('../database/db_connection.php');

require_once('../global.php');
// header("Content-Type: application/json");

if (isset($_POST['user_id']) && isset($_POST['branch_id'])) {
    $userId = intval($_POST['user_id']);
    $newBranchId = intval($_POST['branch_id']);

   
    $branchCheckQuery = "SELECT * FROM branches WHERE id = $newBranchId";
    $branchResult = mysqli_query($conn, $branchCheckQuery);

    if (mysqli_num_rows($branchResult) === 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Branch not found"
        ]);
        exit;
    }

    $updateBranchSql = "UPDATE users SET branch_id = $newBranchId WHERE id = $userId";

    if (mysqli_query($conn, $updateBranchSql)) {
        $branchData = mysqli_fetch_assoc($branchResult);

        echo json_encode([
            "status" => "success",
            "message" => "Branch switched successfully",
            "branch" => [
                "branch_id" => $branchData['id'],
                "branch_name" => $branchData['branch_name'],
                "branch_address" => $branchData['address'],
                "branch_email" => $branchData['email'],
                "phone_number" => $branchData['phone_number'],
                "logo" => $branchData['logo'],
                "created_at" => $branchData['created_at']
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to update user branch"
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Missing user_id or branch_id"
    ]);
}
?>
