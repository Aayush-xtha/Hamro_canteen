<?php
require_once('../database/db_connection.php');
require_once('../global.php');

// Get the Authorization header
$headers = apache_request_headers();
$authorizationHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authorizationHeader) {
    if (preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
        $token = $matches[1];

        // Verify token and get user info
        $sql = "SELECT u.id AS user_id, u.role, u.branch_id 
                FROM users u 
                JOIN tokens t ON u.id = t.user_id 
                WHERE t.token = '$token' 
                AND t.updated_at > NOW() - INTERVAL 1 DAY";

        $result = mysqli_query($conn, $sql);
        $userData = mysqli_fetch_assoc($result);

        if ($userData) {
            $user_id = $userData['user_id'];
            $user_role = $userData['role']; // 'customer' or 'staff'
            $branch_id = $userData['branch_id']; // Staff's branch ID

            // Query for orders based on role
            if ($user_role == 'user') {
                $sql = "SELECT 
                            o.id AS order_id,
                            u.user_name,
                            GROUP_CONCAT(f.food_name SEPARATOR ', ') AS order_items,
                            GROUP_CONCAT(f.price SEPARATOR ', ') AS food_prices,
                            GROUP_CONCAT(f.description SEPARATOR ', ') AS food_descriptions,
                            GROUP_CONCAT(CONCAT('$image_base', f.image) SEPARATOR ', ') AS food_images,
                            o.order_status,
                            p.method AS payment_method
                        FROM orders o
                        JOIN users u ON o.user_id = u.id
                        JOIN order_details od ON o.id = od.order_id
                        JOIN foods f ON od.food_id = f.id
                        LEFT JOIN payments p ON od.id = p.order_detail_id
                        WHERE o.user_id = '$user_id'
                        GROUP BY o.id, u.user_name, o.order_status, p.method
                        ORDER BY o.order_date DESC";
            } elseif ($user_role == 'staff') {
                $sql = "SELECT 
                            o.id AS order_id,
                            u.user_name,
                            GROUP_CONCAT(f.food_name SEPARATOR ', ') AS order_items,
                            GROUP_CONCAT(f.price SEPARATOR ', ') AS food_prices,
                            GROUP_CONCAT(f.description SEPARATOR ', ') AS food_descriptions,
                            GROUP_CONCAT(CONCAT('$image_base', f.image) SEPARATOR ', ') AS food_images,
                            o.order_status,
                            p.method AS payment_method
                        FROM orders o
                        JOIN users u ON o.user_id = u.id
                        JOIN order_details od ON o.id = od.order_id
                        JOIN foods f ON od.food_id = f.id
                        LEFT JOIN payments p ON od.id = p.order_detail_id
                        WHERE od.branch_id = '$branch_id'
                        GROUP BY o.id, u.user_name, o.order_status, p.method
                        ORDER BY o.order_date DESC";
            } else {
                echo json_encode(["status" => "error", "message" => "Unauthorized role"]);
                exit;
            }

            $result = mysqli_query($conn, $sql);
            $orders = mysqli_fetch_all($result, MYSQLI_ASSOC);

            if (!empty($orders)) {
                // Process the order data to include food details in a structured format
                $order_data = [];
                foreach ($orders as $order) {
                    // Convert food details (names, prices, descriptions, images) into an array
                    $food_names = explode(', ', $order['order_items']);
                    $food_prices = explode(', ', $order['food_prices']);
                    $food_descriptions = explode(', ', $order['food_descriptions']);
                    $food_images = explode(', ', $order['food_images']);
                    
                    $food_details = [];
                    for ($i = 0; $i < count($food_names); $i++) {
                        $food_details[] = [
                            'food_name' => $food_names[$i],
                            'price' => $food_prices[$i],
                            'description' => $food_descriptions[$i],
                            'image' => $food_images[$i]
                        ];
                    }

                    $order['food_details'] = $food_details; // Add the food details to the order
                    unset($order['order_items'], $order['food_prices'], $order['food_descriptions'], $order['food_images']); // Clean up unnecessary fields

                    $order_data[] = $order;
                }

                echo json_encode(["status" => "success", "message" => "Orders retrieved successfully", "data" => $order_data]);
            } else {
                echo json_encode(["status" => "error", "message" => "No orders found"]);
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
