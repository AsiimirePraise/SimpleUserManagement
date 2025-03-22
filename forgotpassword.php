<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$conn = new mysqli("localhost", "root", "", "usermanagement");

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format";
    } else {
        // Check if email exists in database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            $error_message = "Email not found in our records";
        } else {
            // Generate a token for password reset
            $token = bin2hex(random_bytes(16));
            $expiry = date('Y-m-d H:i:s', time() + 3600);
            // Store token in database but first delete
            $delete_stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE email = ?");
            $delete_stmt->bind_param("s", $email);
            $delete_stmt->execute();
            // Insert new token
            $insert_stmt = $conn->prepare("INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $email, $token, $expiry);
            $insert_stmt->execute();
            $mail = new PHPMailer(true);

            try {
                $mail->SMTPDebug = 0;
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'mepraise2003@gmail.com';
                $mail->Password   = 'visn grxb yjfr osep';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->setFrom('mepraise2003@gmail.com', 'Password Reset');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $reset_link = "http://localhost/Assignments/Assignment2/resetpassword.php?token=$token&email=$email";
                $mail->Body    = 'Click on this link to reset your password: <a href="' . $reset_link . '">Reset Password</a><br><br>This link will expire in 1 hour.';
                $mail->AltBody = 'Copy this link to reset your password: ' . $reset_link . "\n\nThis link will expire in 1 hour.";

                $mail->send();
                $success_message = "Password reset link has been sent to your email";
            } catch (Exception $e) {
                $error_message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
                <h1>Forgot Password?</h1>
                <p>Enter your email to reset your password.</p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your registered email" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn-primary">Reset Password</button>
                </div>

                <div class="form-footer">
                    <p>Remember your password? <a href="login.php">Login</a></p>
                </div>
            </form>
        </div>
    </main>
</body>

</html>