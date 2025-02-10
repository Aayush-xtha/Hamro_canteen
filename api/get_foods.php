<?php
require_once('../database/db_connection.php');
require_once('../global.php');

// Get the Authorization header
$headers = apache_request_headers();
$authorizationHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authorizationHeader) {
    // Extract Bearer token from the Authorization header
    if (preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
        $token = $matches[1];

        // Verify the token in the tokens table (assuming you have a table for tokens)
        $sql = "SELECT * FROM tokens WHERE token = '$token' AND updated_at > NOW() - INTERVAL 1 DAY";
        $result = mysqli_query($conn, $sql);
        $tokenData = mysqli_fetch_assoc($result);

        if ($tokenData) {
            // Token is valid, proceed to fetch foods by category
            if (isset($_GET['category_id'])) {
                $category_id = $_GET['category_id'];

                // Query to get foods according to the category
                $sql = "SELECT f.id, f.food_name, f.price, f.image, f.description, c.id as category_id, c.category_name,
                        b.id as branch_id, b.branch_name
                        FROM foods f
                        INNER JOIN categories c ON f.category_id = c.id
                        INNER JOIN branches b ON f.branch_id = b.id
                        WHERE f.category_id = '$category_id'";

                $result = mysqli_query($conn, $sql);
                $foods = mysqli_fetch_all($result, MYSQLI_ASSOC);

                // Process the foods data
                $foodList = [];
                foreach ($foods as $food) {
                    $food['image'] = !empty($food['image']) ? $image_base . $food['image'] : $image_base . '';
                    $foodList[] = [
                        'food_id' => $food['id'],
                        'food_name' => $food['food_name'],
                        'price' => $food['price'],
                        'category_id'=> $food['category_id'],
                        'category_name' => $food['category_name'],
                        'branch_id'=> $food['branch_id'],
                        'branch_name' => $food['branch_name'],
                        'description' => $food['description'],
                        'food_image' => $food['image']
                    ];
                }

                // Check if there are any foods found
                if (!empty($foodList)) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "Foods retrieved successfully",
                        "data" => $foodList
                    ]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No foods found in this category"]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Category ID is required"]);
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
