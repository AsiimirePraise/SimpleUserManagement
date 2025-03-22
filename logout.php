<?php
session_start();
session_unset();
session_destroy();

header("Location: login.php");
exit();
?>

<!-- 

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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


</body>

</html> -->