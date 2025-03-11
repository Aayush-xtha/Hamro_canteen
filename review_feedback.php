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
if($result->num_rows >0){
    $row = $result->fetch_assoc();
    $branchName = $row['branch_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ratings & Feedback</title>
    <style>
        /* Root Variables */
        :root {
            --mint: #e9f5f2;
            --sage: #cfe7dc;
            --moss: #6b8f71;
            --white: #fff;
            --dark-gray: #333;
            --shadow: rgba(0, 0, 0, 0.1);
            --button-hover: #567a5c;
        }

        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(90deg, var(--mint), var(--sage));
            margin: 0;
            padding: 0;
        }

        button {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        /* Dashboard Layout */
        .dashboard {
            display: flex;
            height: 100vh;
            width: 100%;
        }

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
            background-color: var(--button-hover);
            font-weight: bold;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 40px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-bar {
            display: flex;
            gap: 10px;
        }

        .search-bar input {
            padding: 10px;
            font-size: 1rem;
            border: 1px solid var(--sage);
            border-radius: 5px;
            width: 300px;
        }

        .btn {
            background-color: var(--moss);
            color: var(--white);
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 1rem;
        }

        .btn:hover {
            background-color: var(--button-hover);
        }

        /* Content Box */
        .content {
            background-color: var(--white);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px var(--shadow);
        }

        /* Review Section */
        .review-section {
            margin-top: 20px;
        }

        .food-card {
            background-color: var(--sage);
            border-radius: 8px;
            box-shadow: 0 4px 6px var(--shadow);
            margin-bottom: 20px;
            padding: 15px;
            text-align: left;
        }

        .food-card h3 {
            margin: 0;
            font-size: 1.5rem;
            color: var(--moss);
        }

        .food-card p {
            margin: 5px 0;
            color: var(--dark-gray);
            font-size: 1rem;
        }

        .feedback-btn {
            margin-top: 10px;
            background-color: var(--moss);
            color: var(--white);
            border: none;
            padding: 8px 12px;
            font-size: 0.9rem;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }

        .feedback-btn:hover {
            background-color: var(--button-hover);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            padding: 20px;
            border-radius: 8px;
            width: 300px;
            text-align: center;
            box-shadow: 0 4px 6px var(--shadow);
        }

        .modal-content textarea {
            width: 100%;
            height: 80px;
            margin-bottom: 10px;
            border: 1px solid var(--sage);
            border-radius: 4px;
            padding: 10px;
        }

        .modal-content .submit-btn {
            background-color: var(--moss);
            color: var(--white);
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
        }

        .modal-content .submit-btn:hover {
            background-color: var(--button-hover);
        }
        .branch-logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto 0px auto;
            border: 3px solid white;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        /* Hover Effect - Slight Scale & Glow */
        .branch-logo:hover {
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
        <div class="logo">
            <?php if (!empty($row['logo'])): ?>
                <img src="./uploads/<?php echo $row['logo']; ?>" alt="Branch Logo" class="branch-logo" onclick="openFullScreenLogo('./uploads/<?php echo $row['logo']; ?>')">
            <?php else: ?>
                <span><?php echo $branchName ?></span>
            <?php endif; ?>
        </div>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="category.php">Category Management</a></li>
                <li><a href="product_management.php">Food Management</a></li>
                
                <li><a href="user.php">Users</a></li>
                <li><a href="review_feedback.php" class="active">Ratings & Feedback</a></li>
                <li><a href="report.php">Report</a></li>

                <li><a href="#">Payments</a></li>
                <li><a href="order_history.php">Order History</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Ratings & Feedback</h1>
                <div class="search-bar">
                    <input type="text" placeholder="Search food...">
                    <button class="btn">Search</button>
                </div>
            </div>

            <div class="content">
                <h2>Reviewed Foods</h2>
                <div class="review-section">
                    <div class="food-card">
                        <h3>Pizza Margherita</h3>
                        <p>Rating: ⭐⭐⭐⭐☆</p>
                        <p>Review: "Delicious and fresh!"</p>
                        <button class="feedback-btn" onclick="openModal()">Give Feedback</button>
                    </div>
                    <div class="food-card">
                        <h3>Pizza Margherita</h3>
                        <p>Rating: ⭐⭐⭐⭐☆</p>
                        <p>Review: "Delicious and fresh!"</p>
                        <button class="feedback-btn" onclick="openModal()">Give Feedback</button>
                    </div>
                    <div class="food-card">
                        <h3>Pizza Margherita</h3>
                        <p>Rating: ⭐⭐⭐⭐☆</p>
                        <p>Review: "Delicious and fresh!"</p>
                        <button class="feedback-btn" onclick="openModal()">Give Feedback</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" id="feedbackModal">
        <div class="modal-content">
            <h2>Give Feedback</h2>
            <textarea placeholder="Write your feedback here..."></textarea>
            <button class="submit-btn" onclick="submitFeedback()">Submit</button>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('feedbackModal').classList.add('active');
        }

        function submitFeedback() {
            alert('Feedback submitted successfully!');
            document.getElementById('feedbackModal').classList.remove('active');
        }
    </script>
</body>
</html>
