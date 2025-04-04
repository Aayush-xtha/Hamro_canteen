<?php
require_once('../database/db_connection.php');
require_once('../global.php');

// Check if required fields are set
if (!isset($_POST['food_id'], $_POST['quantity'], $_POST['user_id'], $_POST['branch_id'])) {
    echo json_encode(["status" => "error", "message" => "Food ID, quantity, user ID, and branch ID are required"]);
    exit;
}

$user_id = $_POST['user_id'];
$branch_id = $_POST['branch_id'];
$food_id = $_POST['food_id'];
$quantity = $_POST['quantity'];

// Validate input (ensure numerical values)
if (!is_numeric($user_id) || !is_numeric($branch_id) || !is_numeric($food_id) || !is_numeric($quantity) || $quantity <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid input values"]);
    exit;
}

// Check if food item belongs to the user's branch
$foodSql = "SELECT id, price FROM foods WHERE id = ? AND branch_id = ?";
$stmt = mysqli_prepare($conn, $foodSql);
mysqli_stmt_bind_param($stmt, "ii", $food_id, $branch_id);
mysqli_stmt_execute($stmt);
$foodResult = mysqli_stmt_get_result($stmt);
$foodData = mysqli_fetch_assoc($foodResult);

if (!$foodData) {
    echo json_encode(["status" => "error", "message" => "Food item not available in your branch"]);
    exit;
}

$price = $foodData['price'];
$total_price = $price * $quantity;

// Check if item already exists in cart
$cartCheckSql = "SELECT id, quantity FROM cart WHERE user_id = ? AND food_id = ?";
$stmt = mysqli_prepare($conn, $cartCheckSql);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $food_id);
mysqli_stmt_execute($stmt);
$cartCheckResult = mysqli_stmt_get_result($stmt);
$existingCart = mysqli_fetch_assoc($cartCheckResult);

if ($existingCart) {
    // Update quantity if item exists
    $newQuantity = $existingCart['quantity'] + $quantity;
    $updateCartSql = "UPDATE cart SET quantity = ?, total_price = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $updateCartSql);
    mysqli_stmt_bind_param($stmt, "idi", $newQuantity, $price * $newQuantity, $existingCart['id']);
    $cartResult = mysqli_stmt_execute($stmt);
} else {
    // Insert new item if it does not exist
    $insertCartSql = "INSERT INTO cart (user_id, food_id, quantity, total_price, branch_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertCartSql);
    mysqli_stmt_bind_param($stmt, "iiidi", $user_id, $food_id, $quantity, $total_price, $branch_id);
    $cartResult = mysqli_stmt_execute($stmt);
}

if ($cartResult) {
    echo json_encode(["status" => "success", "message" => "Item added to cart successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to add item to cart"]);
}
?>
