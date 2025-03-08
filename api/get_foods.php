<?php
require_once('../database/db_connection.php');
require_once('../global.php');

if (!isset($_GET['branch_id'])) {
    echo json_encode(["status" => "error", "message" => "Branch ID is required"]);
    exit();
}

$branch_id = mysqli_real_escape_string($conn, $_GET['branch_id']);

$sql = "SELECT c.id as category_id, c.category_name, 
               f.id as food_id, f.food_name, f.price, f.image, f.description
        FROM categories c
        LEFT JOIN foods f ON c.id = f.category_id
        WHERE f.branch_id = '$branch_id'
        ORDER BY c.category_name, f.food_name";

$result = mysqli_query($conn, $sql);
$foods = mysqli_fetch_all($result, MYSQLI_ASSOC);

$menu = [];
foreach ($foods as $food) {
    $category_id = $food['category_id'];
    $category_name = $food['category_name'];

    if (!isset($menu[$category_id])) {
        $menu[$category_id] = [
            'category_id' => $category_id,
            'category_name' => $category_name,
            'foods' => []
        ];
    }

    if ($food['food_id']) {
        $menu[$category_id]['foods'][] = [
            'food_id' => $food['food_id'],
            'food_name' => $food['food_name'],
            'price' => $food['price'],
            'description' => $food['description'],
            'food_image' => !empty($food['image']) ? $image_base . $food['image'] : ''
        ];
    }
}

if (!empty($menu)) {
    echo json_encode([
        "status" => "success",
        "message" => "Food menu retrieved successfully",
        "data" => array_values($menu)
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "No foods found for this branch"]);
}
?>
