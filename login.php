<?php
session_start();
$conn = new mysqli("localhost", "root", "", "usermanagement");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
    $password = $_POST["password"];

    if (!$email) {
        die("Invalid email format.");
    }

    $query = "SELECT id, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];

            // Remember Me - Store Token
            if (isset($_POST["remember_me"])) {
                $token = bin2hex(random_bytes(32)); // Generate secure token
                setcookie("remember_token", $token, time() + (86400 * 30), "/"); // 30 days

                // Store token in the database
                $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token) VALUES (?, ?)");
                $stmt->bind_param("is", $user["id"], $token);
                $stmt->execute();
            }

            header("Location: edit.php");
            exit();
        } else {
            $error_message = "Incorrect password.";
        }
    } else {
        $error_message = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/styles.css">
    <link rel="stylesheet" href="./assets/login.css">
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
            <div class="form-container">
                <div class="form-header">
                    <p>Welcome back! Please enter your credentials.</p>
                </div>

                <form action="login.php" method="post">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>

                    <div class="form-group checkbox">
                        <input type="checkbox" id="remember_me" name="remember_me">
                        <label for="remember_me">Remember Me</label>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-primary">Login</button>
                    </div>

                    <div class="form-footer">
                        <p>Don't have an account? <a href="index.php">Register</a></p>
                        <p><a href="forgotpassword.php">Forgot Password?</a></p>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php if (!empty($error_message)): ?>
        <script>
            alert("<?php echo $error_message; ?>");
        </script>
    <?php endif; ?>
</body>

</html>