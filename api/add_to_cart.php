<?php
require_once('../database/db_connection.php');
require_once('../global.php');

// Check if required fields are set
if (!isset($_POST['food_id']) || !isset($_POST['quantity']) || !isset($_POST['user_id']) || !isset($_POST['branch_id'])) {
    echo json_encode(["status" => "error", "message" => "Food ID, quantity, user ID, and branch ID are required"]);
    exit;
}

$user_id = $_POST['user_id'];
$branch_id = $_POST['branch_id'];
$food_id = $_POST['food_id'];
$quantity = $_POST['quantity'];

// Check if food item belongs to the user's branch
$foodSql = "SELECT id, price FROM foods WHERE id = '$food_id' AND branch_id = '$branch_id'";
$foodResult = mysqli_query($conn, $foodSql);
$foodData = mysqli_fetch_assoc($foodResult);

if (!$foodData) {
    echo json_encode(["status" => "error", "message" => "Food item not available in your branch"]);
    exit;
}

$price = $foodData['price'];
$total_price = $price * $quantity;

// Check if item already exists in cart
$cartCheckSql = "SELECT id, quantity FROM cart WHERE user_id = '$user_id' AND food_id = '$food_id'";
$cartCheckResult = mysqli_query($conn, $cartCheckSql);
$existingCart = mysqli_fetch_assoc($cartCheckResult);

if ($existingCart) {
    // Update quantity if item exists
    $newQuantity = $existingCart['quantity'] + $quantity;
    $updateCartSql = "UPDATE cart SET quantity = '$newQuantity', total_price = '$price' * '$newQuantity' WHERE id = '{$existingCart['id']}'";
    $cartResult = mysqli_query($conn, $updateCartSql);
} else {
    // Insert new item if it does not exist
    $insertCartSql = "INSERT INTO cart (user_id, food_id, quantity, total_price, branch_id) 
                      VALUES ('$user_id', '$food_id', '$quantity', '$total_price', '$branch_id')";
    $cartResult = mysqli_query($conn, $insertCartSql);
}

if ($cartResult) {
    echo json_encode(["status" => "success", "message" => "Item added to cart successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to add item to cart"]);
}
?>
