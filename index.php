<?php
session_start();

$conn = new mysqli("localhost", "root", "", "usermanagement");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['register'])) {
    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);
    $confirm_password = filter_input(INPUT_POST, "confirm_password", FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        die("All fields are required!");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format!");
    }

    if ($password !== $confirm_password) {
        die("Your passwords do not match!");
    }

    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    $profile_picture = "default.png";
    if ($_FILES["profile_picture"]["error"] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . "_" . basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Allowed file types
        $allowed_types = ["jpg", "jpeg", "png"];
        if (!in_array($imageFileType, $allowed_types)) {
            die("Invalid file type. Only JPG, JPEG, and PNG are allowed.");
        }

        if ($_FILES["profile_picture"]["size"] > 5 * 1024 * 1024) {
            die("File size too large. Maximum allowed size is 5MB.");
        }

        if (!move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            die("Error uploading file.");
        }

        $profile_picture = $file_name;
    }

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, profile_picture) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password_hashed, $profile_picture);

    if ($stmt->execute()) {
        $_SESSION['register_success'] = "Registration successful! Please log in with your new account.";
        header("Location: login.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/register.css">
    <link rel="stylesheet" href="./assets/styles.css">
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
                    <h1>Create an Account</h1>
                    <p>Join our community today!</p>
                </div>

                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Choose a username" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Create a password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                    </div>

                    <div class="form-group">
                        <label for="profile_picture">Profile Picture (Optional)</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/jpg,image/png">
                            <span class="file-name">No file chosen</span>
                        </div>
                        <small>Max size: 5MB. Allowed formats: JPG, JPEG, PNG</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-primary" name="register">Register</button>
                    </div>

                    <div class="form-footer">
                        <p>Already have an account? <a href="login.php">Log In</a></p>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <script>
        // Display selected filename
        document.getElementById('profile_picture').addEventListener('change', function() {
            var fileName = this.files[0] ? this.files[0].name : 'No file chosen';
            document.querySelector('.file-name').textContent = fileName;
        });

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');

            form.addEventListener('submit', function(event) {
                if (passwordInput.value !== confirmPasswordInput.value) {
                    event.preventDefault();
                    alert('Passwords do not match!');
                    confirmPasswordInput.focus();
                    return false;
                }
            });
        });
    </script>
</body>

</html>