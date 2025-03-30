<?php
require_once('./database/db_connection.php');
require_once('global.php');

session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}
$branch_id = $_SESSION['id'];

// Ensure the connection is established
if (!$conn) {
    die("Database connection error: " . mysqli_connect_error());
}

// Fetch branch details
$sql = "SELECT * FROM branches WHERE id = '$branch_id'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $branchName = $row['branch_name'];
} else {
    die("Error fetching branch details.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Profile</title>
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

    .dashboard {
        display: flex;
        min-height: 100vh;
        background-color: var(--light-gray);
    }

    /* Sidebar Styling */
    .sidebar {
        width: 280px;
        background: linear-gradient(to bottom, var(--moss-dark), var(--moss-light));
        color: var(--white);
        padding: 30px 0;
        box-shadow: 5px 0 15px rgba(0,0,0,0.1);
    }

    .sidebar .logo {
        text-align: center;
        margin-bottom: 40px;
        position: relative;
    }

    .sidebar .logo img {
        width: 180px;
        height: 180px;
        object-fit: cover;
        border-radius: 50%;
        border: 5px solid var(--mint);
        transition: transform 0.3s ease;
    }

    .sidebar .logo img:hover {
        transform: scale(1.05);
    }

    .sidebar .logo span {
        display: block;
        color: var(--white);
        font-size: 1.5em;
        margin-top: 10px;
    }

    .sidebar ul {
        list-style: none;
    }

    .sidebar ul li {
        margin: 15px 0;
        position: relative;
    }

    .sidebar ul li a {
        color: var(--white); 
        text-decoration: none;
        padding: 12px 25px;
        display: block;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }

    .sidebar ul li a::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background-color: var(--mint);
        display: block;
        top: 0;
        left: -100%;
        transition: all 0.3s ease;
        z-index: -1;
    }

    .sidebar ul li a:hover::before,
    .sidebar ul li a.active::before {
        left: 0;
    }

    .sidebar ul li a:hover,
    .sidebar ul li a.active {
        color: var(--moss-dark);
        font-weight: bold;
    }

    /* Main Content Styling */
    .container {
        flex-grow: 1;
        background-color: var(--light-gray);
        overflow-y: auto;
        padding: 30px;
    }

    h1 {
        color: var(--moss-dark);
        margin-bottom: 20px;
        border-bottom: 2px solid var(--mint);
        padding-bottom: 10px;
    }

    /* Profile Sections */
    .profile-section,
    .password-section,
    .activity-section {
        background-color: var(--white);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border: 1px solid var(--gray-border);
    }

    .profile-section h2,
    .password-section h2,
    .activity-section h2 {
        color: var(--moss-light);
        margin-bottom: 20px;
        border-bottom: 2px solid var(--mint);
        padding-bottom: 10px;
    }

    /* Profile Header */
    .profile-header {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
    }

    .profile-image {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 5px solid var(--mint);
        object-fit: cover;
        margin-right: 30px;
    }

    .profile-name {
        flex-grow: 1;
    }

    .profile-name h3 {
        font-size: 24px;
        color: var(--moss-dark);
        margin-bottom: 5px;
    }

    .profile-name p {
        color: var(--dark-gray);
        font-size: 16px;
    }

    /* Profile Details */
    .profile-details {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .detail-item {
        margin-bottom: 15px;
    }

    .detail-item label {
        display: block;
        font-weight: bold;
        color: var(--moss-dark);
        margin-bottom: 5px;
    }

    .detail-item p {
        padding: 10px;
        background-color: var(--light-gray);
        border-radius: 8px;
        color: var(--dark-gray);
    }

    /* Form Styling */
    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        font-weight: bold;
        color: var(--moss-dark);
        margin-bottom: 5px;
    }

    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid var(--gray-border);
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--moss-dark);
        box-shadow: 0 0 0 3px rgba(151,193,169,0.2);
    }

    /* Buttons */
    .btn {
        background-color: var(--moss-dark);
        color: var(--white);
        border: none;
        padding: 12px 20px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: bold;
        margin-right: 10px;
    }

    .btn:hover {
        background-color: var(--mint);
        color: var(--moss-dark);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .btn-danger {
        background-color: var(--accent-red);
    }

    .btn-danger:hover {
        background-color: #c0392b;
        color: var(--white);
    }

    /* Activity Timeline */
    .timeline {
        position: relative;
        margin: 20px 0;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 2px;
        background-color: var(--mint);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 25px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -36px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: var(--moss-dark);
        border: 2px solid var(--mint);
    }

    .timeline-date {
        font-size: 14px;
        color: var(--moss-light);
        margin-bottom: 5px;
    }

    .timeline-content {
        background-color: var(--light-gray);
        padding: 15px;
        border-radius: 8px;
    }

    /* Modal Styling */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
        background-color: var(--white);
        margin: 10% auto;
        padding: 30px;
        border-radius: 15px;
        width: 50%;
        max-width: 600px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }

    .close {
        color: var(--dark-gray);
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover {
        color: var(--moss-dark);
    }

    /* Responsive Design */
    @media screen and (max-width: 1200px) {
        .dashboard {
            flex-direction: column;
        }

        .sidebar {
            width: 100%;
            height: auto;
        }

        .sidebar .logo img {
            width: 120px;
            height: 120px;
        }
    }

    @media screen and (max-width: 768px) {
        .container {
            padding: 15px;
        }

        .profile-header {
            flex-direction: column;
            text-align: center;
        }

        .profile-image {
            margin-right: 0;
            margin-bottom: 20px;
        }

        .profile-details {
            grid-template-columns: 1fr;
        }

        .modal-content {
            width: 90%;
        }
    }
    /* Modal Styling */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        overflow: auto;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal-content {
        background-color: var(--white);
        margin: 5% auto;
        padding: 30px;
        border-radius: 15px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        position: relative;
        animation: slideDown 0.4s ease;
    }

    @keyframes slideDown {
        from { transform: translateY(-50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .close {
        position: absolute;
        top: 20px;
        right: 25px;
        color: var(--dark-gray);
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .close:hover {
        color: var(--moss-dark);
        transform: scale(1.1);
    }

    .modal-content h2 {
        color: var(--moss-dark);
        margin-bottom: 25px;
        text-align: center;
        border-bottom: 2px solid var(--mint);
        padding-bottom: 15px;
        font-size: 24px;
    }

    #editProfileForm {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    #editProfileForm label {
        font-weight: bold;
        color: var(--moss-dark);
        margin-bottom: 5px;
        display: block;
    }

    #editProfileForm input[type="text"],
    #editProfileForm input[type="email"] {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid var(--gray-border);
        border-radius: 8px;
        transition: all 0.3s ease;
        font-size: 16px;
        background-color: var(--light-gray);
    }

    #editProfileForm input[type="file"] {
        width: 100%;
        padding: 10px;
        border: 1px dashed var(--gray-border);
        border-radius: 8px;
        background-color: var(--light-gray);
        transition: all 0.3s ease;
    }

    #editProfileForm input:focus {
        outline: none;
        border-color: var(--moss-dark);
        box-shadow: 0 0 0 3px rgba(151, 193, 169, 0.2);
        background-color: var(--white);
    }

    #editProfileForm .btn {
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
        align-self: center;
    }

    #editProfileForm .btn:hover {
        background-color: var(--mint);
        color: var(--moss-dark);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    /* Logo Preview */
    .logo-preview {
        text-align: center;
        margin: 15px 0;
    }

    .logo-preview img {
        max-width: 120px;
        max-height: 120px;
        border-radius: 50%;
        border: 3px solid var(--mint);
        padding: 3px;
    }

    /* Responsive Design */
    @media screen and (max-width: 768px) {
        .modal-content {
            width: 95%;
            margin: 10% auto;
            padding: 20px;
        }
        
        .modal-content h2 {
            font-size: 20px;
        }
        
        #editProfileForm input {
            font-size: 14px;
        }
        
        .close {
            top: 15px;
            right: 20px;
            font-size: 24px;
        }
    }
    </style>

</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
        <div class="logo">
                <?php if (!empty($row['logo'])): ?>
                    <img src="./uploads/<?php echo $row['logo']; ?>" alt="Branch Logo">
                <?php else: ?>
                    <span><?php echo htmlspecialchars($branchName); ?></span>
                <?php endif; ?>
            </div>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="category.php">Category Management</a></li>
                <li><a href="product_management.php">Food Management</a></li>
                <li><a href="staff.php">Staff</a></li>
                <li><a href="report.php">Report</a></li>
                <li><a href="profile.php" class="active">Profile</a></li>
                <li><a href="order_history.php">Order History</a></li>
            </ul>
        </div>

        <div class="container">
            <h1>Branch Profile</h1>

            <div class="profile-section">
                <h2>Profile Information</h2>
                <div class="profile-header">
                    <img src="./uploads/<?php echo $row['logo']; ?>" alt="Branch Logo" class="profile-image">
                    <div class="profile-name">
                        <h3><?php echo htmlspecialchars($branchName); ?></h3>
                        <p>Branch Administrator</p>
                    </div>
                </div>
                
                <div class="profile-details">
                    <div class="detail-item">
                        <label>Branch Name</label>
                        <p><?php echo htmlspecialchars($row['branch_name']); ?></p>
                    </div>
                    <div class="detail-item">
                        <label>Email Address</label>
                        <p><?php echo htmlspecialchars($row['email']); ?></p>
                    </div>
                    <div class="detail-item">
                        <label>Phone Number</label>
                        <p><?php echo htmlspecialchars($row['phone_number']); ?></p>
                    </div>
                    <div class="detail-item">
                        <label>Address</label>
                        <p><?php echo htmlspecialchars($row['address']); ?></p>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <button class="btn" onclick="openEditModal()">Edit Profile</button>
                </div>
            </div>

            <div class="password-section">
                <h2>Security Settings</h2>
                <form id="passwordForm">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn">Change Password</button>
                </form>
                <p id="passwordMessage"></p>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Profile</h2>
            <form id="editProfileForm" enctype="multipart/form-data">
                <label>Branch Name</label>
                <input type="text" name="branch_name" value="<?php echo htmlspecialchars($row['branch_name']); ?>" required>

                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>

                <label>Phone Number</label>
                <input type="text" name="phone_number" value="<?php echo htmlspecialchars($row['phone_number']); ?>" required>

                <label>Address</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($row['address']); ?>" required>

                <label>Change Logo</label>
                <input type="file" name="logo">

                <button type="submit" class="btn">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal() {
            document.getElementById("editModal").style.display = "block";
        }

        function closeEditModal() {
            document.getElementById("editModal").style.display = "none";
        }
                
        document.querySelector('input[name="logo"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                
                let previewContainer = document.querySelector('.logo-preview');
                if (!previewContainer) {
                    previewContainer = document.createElement('div');
                    previewContainer.className = 'logo-preview';
                    this.parentNode.insertBefore(previewContainer, this.nextSibling);
                }
                
                reader.onload = function(event) {
                    previewContainer.innerHTML = `<img src="${event.target.result}" alt="Logo Preview">`;
                }
                
                reader.readAsDataURL(file);
            }
        });

        document.getElementById("editProfileForm").addEventListener("submit", function(event) {
            event.preventDefault();
            let formData = new FormData(this);

            fetch("update_profile.php", {
                method: "POST",
                body: formData
            }).then(response => response.text()).then(data => {
                alert(data);
                location.reload();
            });
        });

        document.getElementById("passwordForm").addEventListener("submit", function(event) {
            event.preventDefault();
            let formData = new FormData(this);

            fetch("change_password.php", {
                method: "POST",
                body: formData
            }).then(response => response.text()).then(data => {
                document.getElementById("passwordMessage").innerText = data;
            });
        });
    </script>
</body>
</html>
