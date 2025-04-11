<?php
require_once('./database/db_connection.php');
require_once('global.php');

session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}
$branch_id = $_SESSION['id'];

$sql = "SELECT * FROM branches WHERE id = '$branch_id'";
$result = mysqli_query($conn, $sql);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $branchName = $row['branch_name'];
}

$sql = "SELECT * FROM users WHERE branch_id = '$branch_id' AND role = 'Staff'";
$result = mysqli_query($conn, $sql);
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="staff.css">
    <title>Staff Management</title>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <div class="logo">
                <?php if (!empty($row['logo'])): ?>
                    <img src="./uploads/<?php echo $row['logo']; ?>" alt="Branch Logo" class="branch-logo">
                <?php else: ?>
                    <span><?php echo htmlspecialchars($branchName); ?></span>
                <?php endif; ?>
            </div>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="category.php">Category Management</a></li>
                <li><a href="product_management.php">Food Management</a></li>
                <li><a href="staff.php" class="active">Staff</a></li>
                <li><a href="report.php">Report</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="order_history.php">Order History</a></li>
            </ul>
        </div>
        <div class="container">
            <div class="form-section">
                <h1>Add Staff</h1>
                <form action="add_staff.php" method="POST" enctype="multipart/form-data">
                    <input type="text" name="first_name" placeholder="First Name" required>
                    <input type="text" name="last_name" placeholder="Last Name" required>
                    <input type="text" name="user_name" placeholder="Username" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="text" name="address" placeholder="Address" required>
                    <input type="text" name="phone_number" placeholder="Phone Number" required>
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                    <input type="password" name="password" placeholder="Password" required>
                    <input type="file" name="image" required>
                    <button type="submit">Add Staff</button>
                </form>
            </div>

            <div class="list-section">
                <h2>Staff List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $staff): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($staff['first_name'] . " " . $staff['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($staff['email']); ?></td>
                            <td><?php echo htmlspecialchars($staff['phone_number']); ?></td>
                            <td><?php echo htmlspecialchars($staff['gender']); ?></td>
                            <td><?php echo htmlspecialchars($staff['role']); ?></td>
                            <td>
                                <button onclick="deleteStaff(<?php echo $staff['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function deleteStaff(id) {
            if (confirm("Are you sure you want to delete this staff member?")) {
                window.location.href = "delete_staff.php?id=" + id;
            }
        }
    </script>
</body>
</html>