<?php
session_start();
require_once('./database/db_connection.php');

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $date = date("Y-m-d H:i:s");

    $sql = "SELECT * FROM branches WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password'])) {
                // Generate a secure token
                $token = bin2hex(random_bytes(32)); // Generate a 64 character token

                // Save the token in the tokens table
                $branch_id = $row['id'];
                $insertTokenSql = "INSERT INTO tokens (branch_id, token, created_at, updated_at) VALUES ('$branch_id', '$token', '$date', '$date')";
                $insertTokenResult = mysqli_query($conn, $insertTokenSql);

                if ($insertTokenResult) {
                    // Set session variables
                    $_SESSION['id'] = $row['id'];
                    $_SESSION['branch_name'] = $row['branch_name'];
                    $_SESSION['token'] = $token;

                    // Redirect to the admin dashboard
                    $_SESSION['flash_message'] = "Login Successful!";
                    $_SESSION['flash_status'] = "success";
                    header("Location: dashboard.php");
                    exit();
                } else {
                    // Error saving token
                    $_SESSION['flash_message'] = "Failed to create login token.";
                    $_SESSION['flash_status'] = "error";
                    header("Location: index.php?error=token_error");
                    exit();
                }
            } else {
                $_SESSION['flash_message'] = "Incorrect password!";
                $_SESSION['flash_status'] = "error";
                header("Location: index.php?error=incorrect_password");
                exit();
            }
        }
    } else {
        // User not found
        $_SESSION['flash_message'] = "No account found with this email!";
        $_SESSION['flash_status'] = "error";
        header("Location: index.php?error=no_account");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
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
    }

    /* Login Card */
    .login-card {
        padding: 40px;
        width: 60%;
    }

    /* Heading */
    h2 {
        color: var(--moss-dark);
        margin-bottom: 30px;
        text-align: center;
        font-size: 28px;
        font-weight: bold;
        position: relative;
        padding-bottom: 15px;
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
        gap: 20px;
    }

    /* Input Groups */
    .input-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .input-group label {
        color: var(--moss-dark);
        font-weight: 600;
        font-size: 16px;
    }

    .input-group input {
        width: 100%;
        padding: 14px 16px;
        border: 1px solid var(--gray-border);
        border-radius: 8px;
        transition: all 0.3s ease;
        font-size: 16px;
        background-color: var(--light-gray);
    }

    .input-group input:focus {
        outline: none;
        border-color: var(--moss-dark);
        box-shadow: 0 0 0 3px rgba(151, 193, 169, 0.2);
        background-color: var(--white);
    }

    /* Login Button */
    .login-button {
        background-color: var(--moss-dark);
        color: var(--white);
        border: none;
        padding: 16px 20px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: bold;
        font-size: 16px;
        margin-top: 10px;
    }

    .login-button:hover {
        background-color: var(--mint);
        color: var(--moss-dark);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    /* Registration Link */
    .registration-link {
        text-align: center;
        margin-top: 15px;
    }

    .registration-link a {
        color: var(--moss-light);
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 15px;
    }

    .registration-link a:hover {
        color: var(--moss-dark);
        text-decoration: underline;
    }

    /* Flash Messages */
    .flash-message {
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 500;
        text-align: center;
    }

    .flash-message.success {
        background-color: var(--accent-green);
        color: var(--white);
    }

    .flash-message.error {
        background-color: var(--accent-red);
        color: var(--white);
    }

    /* Responsive Design */
    @media screen and (max-width: 768px) {
        .main-container {
            flex-direction: column;
            max-width: 450px;
        }
        
        .logo-card, .login-card {
            width: 100%;
            padding: 30px 20px;
        }
        
        .logo-card {
            padding-bottom: 40px;
        }
        
        h2 {
            font-size: 24px;
        }
        
        .input-group input, 
        .login-button {
            font-size: 14px;
            padding: 12px 14px;
        }
    }
  </style>
</head>
<body>
  <div class="main-container">
    <!-- Logo Card -->
    <div class="logo-card">
      <img src="./uploads/logo.png" alt="Kina aakoo Logo" onerror="this.src='uploads/Screenshot 2025-03-11 120820.png">
      <h1>Hamro Canteen</h1>
      <p>Canteen Management System</p>
    </div>
    
    <!-- Login Card -->
    <div class="login-card">
      <h2>Sign In</h2>
      <form action="index.php" method="POST" enctype="multipart/form-data">
        <?php if(isset($_SESSION['flash_message'])): ?>
          <div class="flash-message <?php echo $_SESSION['flash_status']; ?>">
              <?php echo $_SESSION['flash_message']; ?>
          </div>
          <?php 
          // Clear the flash message after displaying it
          unset($_SESSION['flash_message']);
          unset($_SESSION['flash_status']);
          ?>
        <?php endif; ?>
        
        <div class="input-group">
          <label for="email">Email:</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>
        <div class="input-group">
          <label for="password">Password:</label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>
        <button type="submit" class="login-button">Sign In</button>
        <div class="registration-link">
          <a href="register.php">Don't have an account? Register</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>