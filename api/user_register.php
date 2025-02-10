<?php
require_once('../database/db_connection.php');
require_once('../global.php');

if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['phone_number']) && isset($_POST['role']) 
        && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirm_password']) && isset($_POST['branch_id'])) { 
        
        $fname = $_POST['first_name'];
        $lname = $_POST['last_name'];
        $phone = $_POST['phone_number'];
        $email = $_POST['email'];
        $gender = $_POST['gender'];
        $password = $_POST['password'];
        $role = $_POST['role'];
        $confirmPassword = $_POST['confirm_password'];
        $branch_id = $_POST['branch_id']; 
        $date = date("Y-m-d H:i:s");

        // Check if the email already exists
        $checkEmailSql = "SELECT * FROM users WHERE email = '$email'";
        $emailResult = mysqli_query($conn, $checkEmailSql);

        if ($emailResult && mysqli_num_rows($emailResult) > 0) {
            echo json_encode([ 
                "status" => "error", 
                "message" => "Email already exists!"
            ]);
            exit();
        } elseif ($password !== $confirmPassword) {
            echo json_encode([ 
                "status" => "error", 
                "message" => "Password does not match"
            ]);
            exit();
        } else {
            // Check if the branch_id exists in the branches table
            $checkBranchSql = "SELECT * FROM branches WHERE id = '$branch_id'";
            $branchResult = mysqli_query($conn, $checkBranchSql);

            if ($branchResult && mysqli_num_rows($branchResult) == 0) {
                echo json_encode([ 
                    "status" => "error", 
                    "message" => "Branch does not exist!"
                ]);
                exit();
            }

            $hashedPassword = password_hash($confirmPassword, PASSWORD_DEFAULT);

            // Check if an image is uploaded
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $filename = $_FILES["image"]["name"];
                $tempname = $_FILES["image"]["tmp_name"];
                $folder = "../uploads/" . $filename;

                if (move_uploaded_file($tempname, $folder)) {
                    $insertSql = "INSERT INTO users (first_name, last_name, phone_number, email, password, role, gender, branch_id, image) 
                                  VALUES ('$fname','$lname','$phone','$email', '$hashedPassword', '$role', '$gender', '$branch_id', '$filename')";
                }
            } else {
                $insertSql = "INSERT INTO users (first_name, last_name,phone_number, email, password, role, gender, branch_id) 
                              VALUES ('$fname','$lname','$phone','$email', '$hashedPassword', '$role', '$gender', '$branch_id')";
            }

            $result = mysqli_query($conn, $insertSql);

            if ($result) {
                // Get the registered user ID
                $userId = mysqli_insert_id($conn);
                
                // Generate and store token
                $token = bin2hex(random_bytes(32)); 
                $insertTokenSql = "INSERT INTO tokens (user_id, token, created_at, updated_at) VALUES ('$userId', '$token', '$date', '$date')";
                mysqli_query($conn, $insertTokenSql);

                $getUserSql = "SELECT u.id, u.first_name, u.last_name, u.phone_number, u.email, u.gender, u.image, u.role, u.branch_id, b.branch_name, t.token 
                            FROM users u 
                            LEFT JOIN tokens t ON u.id = t.user_id 
                            LEFT JOIN branches b ON u.branch_id = b.id
                            WHERE u.id = $userId";
                $userResult = mysqli_query($conn, $getUserSql);
                $userData = mysqli_fetch_assoc($userResult);

                if (!empty($userData['image'])) {
                    $userData['image'] = $image_base . $userData['image'];
                } else {
                    $userData['image'] = $image_base; 
                }

                echo json_encode([ 
                    "status" => "success",
                    "message" => "User registered successfully!",
                    "data" => $userData
                ]);


            } else {
                echo json_encode([ 
                    "status" => "error", 
                    "message" => "User registration failed!"
                ]);
            }
            exit();
        }
    } else {
        echo json_encode([ 
            "status" => "error", 
            "message" => "Fill the form"
        ]);
        exit();
    }
?>
