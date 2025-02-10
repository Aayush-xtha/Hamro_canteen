<?php
require_once('../database/db_connection.php');
require_once('../global.php');

// Get token from authorization header
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if ($token && isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) 
    && isset($_POST['phone_number']) && isset($_POST['gender']) && isset($_POST['branch_id'])) {

    $firstName = mysqli_real_escape_string($conn, $_POST['first_name']);
    $lastName = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $branch_id = mysqli_real_escape_string($conn, $_POST['branch_id']);

    // Get user ID from token
    $checkTokenSql = "SELECT user_id FROM tokens WHERE token = '$token'";
    $tokenResult = mysqli_query($conn, $checkTokenSql);

    if ($tokenResult && mysqli_num_rows($tokenResult) > 0) {
        $tokenData = mysqli_fetch_assoc($tokenResult);
        $userId = $tokenData['user_id'];

        // Check if email is already used by another user
        $checkEmailSql = "SELECT * FROM users WHERE email = '$email' AND id != '$userId'";
        $emailResult = mysqli_query($conn, $checkEmailSql);

        if ($emailResult && mysqli_num_rows($emailResult) > 0) {
            echo json_encode(["status" => "error", "message" => "Email already in use!"]);
            exit();
        }

        // Get current user data to check the existing image
        $getUserSql = "SELECT image FROM users WHERE id='$userId'";
        $userResult = mysqli_query($conn, $getUserSql);
        $userData = mysqli_fetch_assoc($userResult);
        $oldImage = $userData['image'];

        // Prepare update query
        $updateSql = "UPDATE users SET 
                      first_name='$firstName', 
                      last_name='$lastName', 
                      email='$email', 
                      phone_number='$phone', 
                      gender='$gender', 
                      branch_id='$branch_id' 
                      WHERE id='$userId'";

        if (mysqli_query($conn, $updateSql)) {
            // Check if an image is uploaded
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $filename = time() . "_" . basename($_FILES["image"]["name"]);
                $tempname = $_FILES["image"]["tmp_name"];
                $folder = "../uploads/" . $filename;

                // Delete old image if exists
                if (!empty($oldImage) && file_exists("../uploads/" . $oldImage)) {
                    unlink("../uploads/" . $oldImage);
                }

                if (move_uploaded_file($tempname, $folder)) {
                    $updateImageSql = "UPDATE users SET image='$filename' WHERE id='$userId'";
                    mysqli_query($conn, $updateImageSql);
                }
            }

            // Get updated user data including token
            $getUserSql = "SELECT u.id, u.first_name, u.last_name, u.email, u.phone_number, 
                                  u.gender, u.image, u.branch_id, b.branch_name, t.token 
                           FROM users u 
                           LEFT JOIN tokens t ON u.id = t.user_id 
                           LEFT JOIN branches b ON u.branch_id = b.id 
                           WHERE u.id = '$userId'";

            $userResult = mysqli_query($conn, $getUserSql);
            $userData = mysqli_fetch_assoc($userResult);

            if (!empty($userData['image'])) {
                $userData['image'] = $image_base . $userData['image'];
            } else {
                $userData['image'] = $image_base;
            }

            echo json_encode([
                "status" => "success",
                "message" => "Profile updated successfully!",
                "data" => $userData
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Profile update failed!"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid token!"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "All fields are required!"]);
}
?>
