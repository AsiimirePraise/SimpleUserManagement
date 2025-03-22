<?php
$conn = new mysqli("localhost", "root", "", "usermanagement");
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';
$error_message = '';
$success_message = '';
if (empty($token) || empty($email)) {
    $error_message = "Invalid password reset link. Missing parameters.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate passwords
    if (empty($password)) {
        $error_message = "Password cannot be empty";
    } elseif (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match";
    } else {
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update_stmt->bind_param("ss", $hashed_password, $email);

        if ($update_stmt->execute()) {
            // Delete the used token
            $delete_stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE email = ?");
            $delete_stmt->bind_param("s", $email);
            $delete_stmt->execute();

            $success_message = "Your password has been reset successfully. You can now <a href='login.php'>login</a> with your new password.";
        } else {
            $error_message = "Failed to update password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/styles.css">
    <link rel="stylesheet" href="./assets/forgot password.css">
</head>

<body style="background-color:lightskyblue ;">
    <header>
        <nav>
            <h1>User Management System</h1>
            <ul>
                <li><a href="./index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="./login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="./logout.php"><i class="fas fa-sign-in-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="container">
            <div class="form-header">
                <h1>Reset Your Password</h1>
                <p>Please enter your new password.</p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php else: ?>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?token=' . urlencode($token) . '&email=' . urlencode($email)); ?>" method="post">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your new password (min 8 characters)" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-primary">Reset Password</button>
                    </div>
                </form>
            <?php endif; ?>

            <?php if (!empty($error_message) && strpos($error_message, "Invalid or expired") !== false): ?>
                <div class="form-footer">
                    <p>Need a new reset link? <a href="forgotpassword.php">Request Again</a></p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>