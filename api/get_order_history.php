<?php
require_once('../database/db_connection.php');
require_once('../global.php');

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch user details
    $userQuery = "SELECT id, first_name, last_name, email, phone_number, gender, role, image FROM users WHERE id = '$user_id'";
    $userResult = mysqli_query($conn, $userQuery);
    $userData = mysqli_fetch_assoc($userResult);

    if (!$userData) {
        echo json_encode([
            'status' => 'error',
            'message' => 'User not found.'
        ]);
        exit;
    }

    $user_type = $userData['role'];

    if($user_type === 'staff'){
        $orderQuery = "
        SELECT 
                o.id AS order_id,
                o.total_amount,
                o.order_status,
                o.order_date,
                u.id AS user_id,
                CONCAT(u.first_name, ' ', u.last_name) AS user_name,
                u.email AS user_email,
                u.phone_number,
                u.gender,
                u.image AS user_image,
                od.food_id,
                f.food_name,
                f.price AS food_price,
                f.image AS food_image,
                od.quantity,
                od.rate,
                od.sum_total,
                p.method AS payment_method,
                p.amount AS payment_amount,
                p.payment_date
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN order_details od ON o.id = od.order_id
            LEFT JOIN foods f ON od.food_id = f.id
            LEFT JOIN payments p ON od.id = p.order_detail_id";
    }else{
        $orderQuery = "
        SELECT 
            o.id AS order_id,
            o.total_amount,
            o.order_status,
            o.order_date,
            u.id AS user_id,
            CONCAT(u.first_name, ' ', u.last_name) AS user_name,
            u.email AS user_email,
            u.phone_number,
            u.gender,
            u.image AS user_image,
            od.food_id,
            f.food_name,
            f.price AS food_price,
            f.image AS food_image,
            od.quantity,
            od.rate,
            od.sum_total,
            p.method AS payment_method,
            p.amount AS payment_amount,
            p.payment_date
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN order_details od ON o.id = od.order_id
        LEFT JOIN foods f ON od.food_id = f.id
        LEFT JOIN payments p ON od.id = p.order_detail_id
        WHERE o.user_id = '$user_id'";
    }

    $orderResult = mysqli_query($conn, $orderQuery);

    if ($orderResult && mysqli_num_rows($orderResult) > 0) {
        $orders = [];

        while ($row = mysqli_fetch_assoc($orderResult)) {
            $order_id = $row['order_id'];

            if (!isset($orders[$order_id])) {
                $orders[$order_id] = [
                    'order_id' => $row['order_id'],
                    'total_amount' => $row['total_amount'],
                    'order_status' => $row['order_status'],
                    'order_date' => $row['order_date'],
                    'user_details' => [
                        'user_id' => $row['user_id'],
                        'name' => $row['user_name'],
                        'email' => $row['user_email'],
                        'phone_number' => $row['phone_number'],
                        'gender' => $row['gender'],
                        'image' => !empty($row['user_image']) ? $image_base . $row['user_image'] : null
                    ],
                    'items' => [],
                    'payment' => [
                        'method' => $row['payment_method'],
                        'amount' => $row['payment_amount'],
                        'payment_date' => $row['payment_date']
                    ]
                ];
            }

            if (!empty($row['food_id'])) {
                $orders[$order_id]['items'][] = [
                    'food_id' => $row['food_id'],
                    'food_name' => $row['food_name'],
                    'food_image' => $image_base . $row['food_image'],
                    'quantity' => $row['quantity'],
                    'rate' => $row['rate'],
                    'sum_total' => $row['sum_total']
                ];
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Order details retrieved successfully!',
            'data' => array_values($orders),
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No orders found for this user.',
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'User ID is required.'
    ]);
}
?>
