<?php
require_once('../database/db_connection.php');
require_once('../global.php');

// Query to get all branches
$sql = "SELECT id, branch_name, address, email, phone_number, logo, created_at FROM branches";
$result = mysqli_query($conn, $sql);
$branches = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Process branch data
$branchList = [];
foreach ($branches as $branch) {
    $branch['logo'] = !empty($branch['logo']) ? $image_base . $branch['logo'] : $image_base . 'default_logo.png';
    $branchList[] = [
        'branch_id' => $branch['id'],
        'branch_name' => $branch['branch_name'],
        'address' => $branch['address'],
        'email' => $branch['email'],
        'phone_number' => $branch['phone_number'],
        'logo' => $branch['logo'],
        'created_at' => $branch['created_at']
    ];
}

// Check if there are any branches found
if (!empty($branchList)) {
    echo json_encode([
        "status" => "success",
        "message" => "Branches retrieved successfully",
        "data" => $branchList
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "No branches found"]);
}
?>
