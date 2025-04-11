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
$item_total = $price * $quantity;

// Check if a cart already exists for the user (you can make it per-branch if needed)
$cartSql = "SELECT id FROM cart WHERE user_id = ? AND branch_id = ?";
$stmt = mysqli_prepare($conn, $cartSql);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $branch_id);
mysqli_stmt_execute($stmt);
$cartResult = mysqli_stmt_get_result($stmt);
$cart = mysqli_fetch_assoc($cartResult);

if ($cart) {
    $cart_id = $cart['id'];
} else {
    // Create a new cart
    $insertCartSql = "INSERT INTO cart (user_id, branch_id, total_price) VALUES (?, ?, 0)";
    $stmt = mysqli_prepare($conn, $insertCartSql);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $branch_id);
    mysqli_stmt_execute($stmt);
    $cart_id = mysqli_insert_id($conn);
}

// Check if food item already exists in cart_items
$cartItemSql = "SELECT id, quantity FROM cart_items WHERE cart_id = ? AND food_id = ?";
$stmt = mysqli_prepare($conn, $cartItemSql);
mysqli_stmt_bind_param($stmt, "ii", $cart_id, $food_id);
mysqli_stmt_execute($stmt);
$cartItemResult = mysqli_stmt_get_result($stmt);
$existingItem = mysqli_fetch_assoc($cartItemResult);

if ($existingItem) {
    $newQuantity = $existingItem['quantity'] + $quantity;
    $newItemTotal = $newQuantity * $price;

    // Update quantity and item total
    $updateItemSql = "UPDATE cart_items SET quantity = ?, item_total_price = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $updateItemSql);
    mysqli_stmt_bind_param($stmt, "idi", $newQuantity, $newItemTotal, $existingItem['id']);
    $itemResult = mysqli_stmt_execute($stmt);
} else {
    // Insert new item into cart_items
    $insertItemSql = "INSERT INTO cart_items (cart_id, food_id, quantity, item_total_price) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertItemSql);
    mysqli_stmt_bind_param($stmt, "iiid", $cart_id, $food_id, $quantity, $item_total);
    $itemResult = mysqli_stmt_execute($stmt);
}

// Update total price of cart
$updateCartTotalSql = "
    UPDATE cart 
    SET total_price = (SELECT SUM(item_total_price) FROM cart_items WHERE cart_id = ?) 
    WHERE id = ?
";
$stmt = mysqli_prepare($conn, $updateCartTotalSql);
mysqli_stmt_bind_param($stmt, "ii", $cart_id, $cart_id);
mysqli_stmt_execute($stmt);

if ($itemResult) {
    echo json_encode(["status" => "success", "message" => "Item added/updated in cart successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to add/update item in cart"]);
}
?>
