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
        /* Root Variables */
        :root {
            --white: #ffffff;
            --moss-dark: #2a5848;
            --moss-light: #3c7a66;
            --mint: #97c1a9;
            --mint-light: #b8d8c0;
            --light-gray: #f7f9f8;
            --gray-border: #e0e6e3;
            --dark-gray: #333;
            --accent-red: #e74c3c;
            --accent-green: #2ecc71;
        }

        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-gray);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background: linear-gradient(135deg, var(--moss-light) 0%, var(--mint-light) 100%);
        }

        /* Main Container Card */
        .main-container {
            background-color: var(--white);
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 900px;
            border: 1px solid var(--gray-border);
            display: flex;
            overflow: hidden;
        }

        /* Logo Card */
        .logo-card {
            background-color: var(--moss-dark);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 40%;
            color: var(--white);
            text-align: center;
        }

        .logo-card img {
            max-width: 150px;
            height: auto;
            margin-bottom: 20px;
        }

        .logo-card h1 {
            font-size: 28px;
            margin-bottom: 15px;
        }

        .logo-card p {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
        }

        /* Registration Card */
        .register-card {
            padding: 40px;
            width: 60%;
            overflow-y: auto;
            max-height: 650px;
        }

        /* Heading */
        h2 {
            color: var(--moss-dark);
            margin-bottom: 25px;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
            font-size: 28px;
        }

        h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--mint);
            border-radius: 3px;
        }

        /* Form Styling */
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        /* Input Fields */
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--gray-border);
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 16px;
            background-color: var(--light-gray);
        }

        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px dashed var(--gray-border);
            border-radius: 8px;
            background-color: var(--light-gray);
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--moss-dark);
            box-shadow: 0 0 0 3px rgba(151, 193, 169, 0.2);
            background-color: var(--white);
        }

        /* Button */
        button {
            background-color: var(--moss-dark);
            color: var(--white);
            border: none;
            padding: 14px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
            margin-top: 10px;
            font-size: 16px;
        }

        button:hover {
            background-color: var(--mint);
            color: var(--moss-dark);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Login Link */
        .login-btn {
            color: var(--moss-light);
            text-decoration: none;
            text-align: center;
            display: block;
            margin-top: 15px;
            transition: all 0.3s ease;
            font-size: 15px;
        }

        .login-btn:hover {
            color: var(--moss-dark);
            text-decoration: underline;
        }

        /* Logo Preview */
        .logo-preview {
            margin-top: 10px;
            text-align: center;
        }

        .logo-preview img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 50%;
            border: 3px solid var(--mint);
            padding: 3px;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .main-container {
                flex-direction: column;
                max-width: 500px;
            }
            
            .logo-card, .register-card {
                width: 100%;
                padding: 30px 20px;
            }
            
            .logo-card {
                padding-bottom: 40px;
            }
            
            .register-card {
                max-height: none;
                overflow-y: visible;
            }
            
            h2 {
                font-size: 24px;
            }
            
            input, button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Logo Card -->
        <div class="logo-card">
            <img src="./uploads/logo.png" alt="Kina aakoo Logo" onerror="this.src='https://via.placeholder.com/150x150?text=Kina+aakoo';this.onerror='';">
            <h1>Hamro Canteen</h1>
            <p>Join our canteen management platform to streamline your operations and enhance customer experience.</p>
            <p style="margin-top: 20px;">Create your account to get started!</p>
        </div>
        
        <!-- Registration Card -->
        <div class="register-card">
            <h2>Register</h2>
            <form action="register.php" method="POST" enctype="multipart/form-data">
                <input type="text" name="branch_name" placeholder="Branch Name" required>
                <input type="text" name="address" placeholder="Address" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="tel" name="phone_number" placeholder="Phone Number" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                
                <div>
                    <label for="logo" style="display: block; margin-bottom: 8px; color: var(--moss-dark); font-weight: 600;">Branch Logo:</label>
                    <input type="file" id="logo" name="logo" accept="image/*" required>
                </div>
                
                <div id="logo-preview-container" class="logo-preview" style="display: none;"></div>
                
                <button type="submit">Register</button>
                <a href="index.php" class="login-btn">Already have an account? Login</a>
            </form>
        </div>
    </div>
    
    <!-- Script for logo preview -->
    <script>
        document.getElementById('logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                const previewContainer = document.getElementById('logo-preview-container');
                
                reader.onload = function(event) {
                    previewContainer.innerHTML = `<img src="${event.target.result}" alt="Logo Preview">`;
                    previewContainer.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>