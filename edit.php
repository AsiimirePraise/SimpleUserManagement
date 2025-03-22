<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Connect to the database
$conn = new mysqli("localhost", "root", "", "usermanagement");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details
$sql = "SELECT username, email, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found!";
    exit();
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/styles.css">
    <link rel="stylesheet" href="./assets/edit.css">
</head>

<body style="background-color:lightskyblue ;">
    <header>
        <nav>
            <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?></h2>
            <ul>
                <li><a href="./index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="./login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="./login.php"><i class="fas fa-sign-in-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="dashboard-container">
            <div class="welcome-section">
                <center>
                    <h2>Welcome <?php echo htmlspecialchars($user['username']); ?></h2>
                </center>
                <!-- Profile Picture -->
                <div class="profile-picture">
                    <center><img src="<?php echo $user['profile_picture'] ? 'uploads/' . $user['profile_picture'] : 'uploads/default.png'; ?>" alt="Profile Picture"></center>
                </div>
                <p>Hello, <?php echo htmlspecialchars($user['username']); ?>! You can manage your profile and account settings here.</p>
            </div>
            <!-- Edit Account Section -->
            <div class="edit-section">
                <br><br>
                <h3 class="sectionA">Edit Account</h3>
                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                    <div class="alert alert-success">Profile updated successfully!</div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <?php
                        switch ($_GET['error']) {
                            case 'upload_failed':
                                echo "Failed to upload profile picture. Please try again.";
                                break;
                            case 'invalid_type':
                                echo "Invalid file type. Please upload JPG, JPEG, PNG, or GIF images only.";
                                break;
                            default:
                                echo "An error occurred. Please try again.";
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <form action="process_edit.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    <br>
                    <div class="form-group">
                        <label for="username">New Full Name</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">New Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">New Password (Optional)</label>
                        <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">
                    </div>

                    <div class="form-group">
                        <label for="profile_picture">Upload Profile Picture</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>

            <!-- Delete Account Section -->
            <div class="delete-section">
                <h3>Delete Account</h3>
                <p>Warning: This action is irreversible!</p>
                <form action="delete_account.php" method="post" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone!');">
                    <button type="submit" class="btn-danger">Delete My Account</button>
                </form>
            </div>
        </div>
    </main>
</body>

</html>