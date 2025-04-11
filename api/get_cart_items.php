
<?php
require_once('../database/db_connection.php');
require_once('../global.php');

if (isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];
    $sql = "SELECT 
            cart.id AS cart_id,
            cart.user_id,
            cart.branch_id,
            cart.total_price AS cart_total,
            cart.created_at,
            cart_items.id AS cart_item_id,
            cart_items.food_id,
            cart_items.quantity,
            cart_items.item_total_price,
            foods.food_name,
            foods.price AS unit_price,
            foods.image as food_image,
            foods.description
        FROM cart
        JOIN cart_items ON cart.id = cart_items.cart_id
        JOIN foods ON cart_items.food_id = foods.id
        WHERE cart.user_id = $userId";


    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $cartItems = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $row['food_image'] = $image_base . $row['food_image'];
            $cartItems[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $cartItems]);
    } else {
        echo json_encode(["status" => "error", "message" => "No items in cart"]);
    }
    
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'User ID is required.'
    ]);
}
?>
