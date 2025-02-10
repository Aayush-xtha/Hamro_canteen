<?php
session_start();
require('./database/db_connection.php');

if (isset($_POST['branch_name']) && isset($_POST['address']) && isset($_POST['email']) 
    && isset($_POST['phone_number']) && isset($_POST['password']) && isset($_POST['confirm_password'])) {
    

    // Sanitize inputs
    $bname = mysqli_real_escape_string($conn, $_POST['branch_name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $password = $_POST['password'];
    $confirmpassword = $_POST['confirm_password'];
    $date = date("Y-m-d H:i:s");

    // Check if email exists
    $checkEmailSql = "SELECT * FROM branches WHERE email = '$email'";
    $checkResult = mysqli_query($conn, $checkEmailSql);

    if ($checkResult && mysqli_num_rows($checkResult) > 0) {
        $_SESSION['flash_message'] = "Email already exists!";
        header("Location: register.php");
        exit();
    } elseif ($password !== $confirmpassword) {
        $_SESSION['flash_message'] = "Passwords do not match!";
        header("Location: register.php");
        exit();
    } else {
        $hashedConfirmPassword = password_hash($confirmpassword, PASSWORD_DEFAULT);

        // File upload handling
        $filename = NULL;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $filename = basename($_FILES["logo"]["name"]);
            $tempname = $_FILES["logo"]["tmp_name"];
            $folder = "./uploads/" . $filename;

            if (!is_dir("./uploads/")) {
                mkdir("./uploads/", 0777, true);
            }

            move_uploaded_file($tempname, $folder);
        }

        // Insert into database
        if ($filename) {
            $insertSql = "INSERT INTO branches(branch_name, address, phone_number, email, password, logo, created_at) 
                          VALUES ('$bname','$address','$phone','$email', '$hashedConfirmPassword', '$filename', '$date')";
        } else {
            $insertSql = "INSERT INTO branches(branch_name, address, phone_number, email, password, created_at) 
                          VALUES ('$bname','$address','$phone','$email', '$hashedConfirmPassword', '$date')";
        }

        // Execute query
        if (mysqli_query($conn, $insertSql)) {
            $_SESSION['flash_message'] = "User registered successfully!";
            $_SESSION['flash_status'] = "success";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['flash_message'] = "User registration failed!";
            $_SESSION['flash_status'] = "error";
            header("Location: register.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px; 
            text-align: center;
        }

        input, select {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            width: 95%; 
            padding: 10px;
            margin-top: 20px;
            margin-bottom: 30px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }
        .login-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            color: #218838;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
            text-align: center;
            transition: background 0.3s ease;
        }

        .login-btn:hover {
            color: grey;
        }


    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form action="register.php" method="POST" enctype="multipart/form-data">

            
            <input type="text" name="branch_name" placeholder="Branch Name" required>
            <input type="text" name="address" placeholder="Address" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="tel" name="phone_number" placeholder="Phone Number" required>
           
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="file" name="logo" required>
            <button type="submit">Register</button><br>
            <a href="index.php" class="login-btn">Already have an account? Login</a><br>
        </form>
    </div>
</body>
</html>
