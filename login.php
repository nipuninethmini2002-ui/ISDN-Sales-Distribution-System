<?php
session_start();
if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>ISDN Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #94bda5;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .header {
            background: #4CAF50;
            color: #fff;
            padding: 20px 0;
            text-align: center;
            width: 100%;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .header img.logo {
            width: 80px;
            height: auto;
            vertical-align: middle;
            margin-right: 15px;
            border-radius: 10px;
        }

        .header h1 {
            display: inline-block;
            font-size: 28px;
            vertical-align: middle;
            letter-spacing: 1px;
        }

        .login-container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.2);
            width: 350px;
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .login-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .login-container input[type="submit"] {
            width: 100%;
            padding: 10px;
            background: #4CAF50;
            border: none;
            color: #fff;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        .login-container input[type="submit"]:hover {
            background: #45a049;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }

        .success {
            color: green;
            text-align: center;
            margin-bottom: 10px;
        }

        .login-container a {
            text-decoration: none;
            color: #4CAF50;
        }

        .login-container a:hover {
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            padding: 15px 0;
            background: rgba(0,0,0,0.5);
            color: #fff;
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTAn86MfzjoogXI0jNgrcoCu6Dc1ze28tMXEw&s" alt="ISDN Logo" class="logo">
        <h1>ISDN Sales Distribution System</h1>
    </div>

    <div class="login-container">
        <h2>Login</h2>

        <?php
        // signup success message
        if(isset($_SESSION['success'])){
            echo "<div class='success'>".$_SESSION['success']."</div>";
            unset($_SESSION['success']);
        }

        // login error message
        if(isset($_SESSION['login_error'])){
            echo "<div class='error'>".$_SESSION['login_error']."</div>";
            unset($_SESSION['login_error']);
        }
        ?>

        <form action="login_process.php" method="post">
            <label>Username:</label>
            <input type="text" name="username" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <input type="submit" value="Login">
            <p style="margin-top: 15px; text-align:center;">
                Don't have an account? <a href="signup.php">Sign Up</a>
            </p>
        </form>
    </div>

    <div class="footer">
        &copy; 2026 ISDN System | Designed by N&R
    </div>

</body>
</html>
