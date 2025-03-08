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
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="login-container">
    <h2>Kina aakoo</h2>
    <form action="index.php" method="POST" enctype="multipart/form-data">
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
</body>
</html>

