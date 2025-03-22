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

$sql = "SELECT * FROM users WHERE branch_id = '$branch_id' AND role = 'Staff'"; // Filter by role
$result = mysqli_query($conn, $sql);
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management</title>
    <link rel="stylesheet" href="styles.css"> 
</head>
<style>
    :root {
        --mint: #A8D5BA;
        --sage: #C1DAB4;
        --moss: #6D8B74;
        --white: #FFFFFF;
        --gray: #F5F5F5;
        --dark-gray: #3A3A3A;
        --light-gray: #E0E0E0;
        --hover-color: #B4E4CA;
        --active-color: #B0D8C0;
    }

    body {
        margin: 0;
        font-family: 'Arial', sans-serif;
        background-color: var(--gray);
        color: var(--dark-gray);
    }

    .dashboard {
        display: flex;
        height: 100vh;
        width: 100%;
    }

    /* Sidebar */
    .sidebar {
        width: 250px;
        background-color: var(--moss);
        color: var(--white);
        display: flex;
        flex-direction: column;
        padding: 20px;
        position: fixed;
        height: 100%;
    }

    .sidebar .logo {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 30px;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
    }

    .sidebar ul li {
        margin: 15px 0;
    }

    .sidebar ul li a {
        text-decoration: none;
        color: var(--white);
        font-size: 1rem;
        padding: 10px;
        border-radius: 5px;
        display: block;
        transition: background-color 0.3s ease;
    }

    .sidebar ul li a:hover,
    .sidebar ul li a.active {
        background-color: var(--active-color);
        font-weight: bold;
        color: var(--dark-gray);
    }

    /* Content Layout */
    .container {
        padding: 20px;
        margin-left: 270px;
        width: calc(100% - 270px);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
    }

    .container h1 {
        font-size: 2rem;
        color: var(--moss);
        text-align: center;
    }

    /* Form Section */
    .form-section {
        background-color: var(--white);
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin: 20px 0;
        width: 100%;
        max-width: 600px;
    }

    .form-section:hover {
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    }

    .form-section label {
        font-size: 1rem;
        font-weight: bold;
        display: block;
        margin-top: 10px;
        color: var(--dark-gray);
    }

    .form-section input,
    .form-section select {
        width: 100%;
        margin: 8px 0;
        padding: 10px;
        border: 1px solid var(--light-gray);
        border-radius: 5px;
        font-size: 1rem;
    }

    .form-section input:focus,
    .form-section select:focus {
        outline: none;
        border: 2px solid var(--mint);
    }

    .form-section button {
        width: 100%;
        background-color: var(--mint);
        color: var(--white);
        font-weight: bold;
        cursor: pointer;
        padding: 12px;
        margin-top: 15px;
        border: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .form-section button:hover {
        background-color: var(--hover-color);
    }

    /* Staff List Table */
    .list-section {
        background-color: var(--white);
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin: 20px 0;
        width: 100%;
        max-width: 900px;
    }

    .list-section h2 {
        text-align: center;
        color: var(--moss);
    }

    .list-section table {
        width: 100%;
        border-collapse: collapse;
    }

    .list-section table th, 
    .list-section table td {
        padding: 12px;
        border: 1px solid var(--light-gray);
        text-align: center;
        font-size: 1rem;
    }

    .list-section table th {
        background-color: var(--mint);
        color: var(--white);
        text-transform: uppercase;
    }

    .list-section table tbody tr:hover {
        background-color: var(--hover-color);
    }

    .list-section button {
        background-color: var(--moss);
        color: var(--white);
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: background-color 0.3s ease;
    }

    .list-section button:hover {
        background-color: var(--hover-color);
        color: var(--dark-gray);
    }

    /* Profile Image */
    .branch-logo {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        display: block;
        margin: 0 auto 10px auto;
        border: 3px solid white;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }

    .branch-logo:hover {
        transform: scale(1.1);
        box-shadow: 0 0 15px rgba(255, 255, 255, 0.5);
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .container {
            margin-left: 0;
            width: 100%;
            padding: 10px;
        }

        .sidebar {
            width: 200px;
        }
    }

    @media (max-width: 768px) {
        .dashboard {
            flex-direction: column;
        }

        .sidebar {
            width: 100%;
            position: relative;
            height: auto;
            padding: 10px;
        }

        .container {
            width: 100%;
            margin-left: 0;
        }
    }

    @media (max-width: 480px) {
        .form-section,
        .list-section {
            width: 100%;
            padding: 10px;
        }

        .form-section button {
            width: 100%;
        }

        .list-section table th, 
        .list-section table td {
            padding: 8px;
            font-size: 0.9rem;
        }
    }



</style>
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
            <li><a href="user.php" class="active">Users</a></li>
            <li><a href="review_feedback.php">Ratings & Feedback</a></li>
            <li><a href="report.php">Report</a></li>
            <li><a href="#">Payments</a></li>
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
