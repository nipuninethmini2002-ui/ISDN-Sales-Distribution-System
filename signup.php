<?php
session_start();
include 'db.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/vendor/autoload.php';

if(isset($_POST['register'])){

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email    = trim($_POST['email']);
    $role     = $_POST['role'] ?? '';

    $branch   = ($role=='Customer' || $role=='RDC_Staff') ? trim($_POST['branch'] ?? '') : null;

    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');

    if(empty($username) || empty($password) || empty($email) || empty($role)){
        $_SESSION['error'] = "All required fields must be filled!";
        header("Location: signup.php");
        exit();
    }

    if(($role=='Customer' || $role=='RDC_Staff') && empty($branch)){
        $_SESSION['error'] = "Please select a branch!";
        header("Location: signup.php");
        exit();
    }

    if($role=='Customer' && (empty($phone) || empty($address))){
        $_SESSION['error'] = "Phone and Address are required for Customer!";
        header("Location: signup.php");
        exit();
    }

    if($role=='Delivery_Person' && empty($phone)){
        $_SESSION['error'] = "Phone is required for Delivery Person!";
        header("Location: signup.php");
        exit();
    }

    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT user_id FROM users WHERE username=?");
    $check->bind_param("s", $username);
    $check->execute();
    $res = $check->get_result();
    if($res->num_rows > 0){
        $_SESSION['error'] = "Username already exists!";
        header("Location: signup.php");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO users (username,password,email,role,branch) VALUES (?,?,?,?,?)");
    $stmt->bind_param("sssss", $username, $password_hashed, $email, $role, $branch);
    if($stmt->execute()){
        $new_user_id = $stmt->insert_id;

        if($role=='Customer'){
            $stmt2 = $conn->prepare("INSERT INTO customers (user_id, customer_branch, phone, address) VALUES (?,?,?,?)");
            $stmt2->bind_param("isss", $new_user_id, $branch, $phone, $address);
            $stmt2->execute();
            $stmt2->close();
        }

        if($role=='Delivery_Person'){
            $vehicle_type = null;
            $status = 'Active';
            $created_at = date("Y-m-d H:i:s");
            $stmt2 = $conn->prepare("INSERT INTO delivery_person (user_id, phone, vehicle_type, status, created_at) VALUES (?,?,?,?,?)");
            $stmt2->bind_param("issss", $new_user_id, $phone, $vehicle_type, $status, $created_at);
            $stmt2->execute();
            $stmt2->close();
        }

        $_SESSION['success'] = "Account created successfully! Please login.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Registration failed!";
        header("Location: signup.php");
        exit();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>ISDN Sign Up</title>
    <style>
        body { font-family: Arial; background: #94bda5; display: flex; flex-direction: column; align-items: center; min-height: 100vh; padding: 20px; }
        .header { background: #4CAF50; color: #fff; padding: 20px 0; text-align: center; width: 100%; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
        .header h1 { font-size: 28px; }
        .signup-container { background: #fff; padding: 30px 40px; border-radius: 10px; box-shadow: 0px 0px 10px rgba(0,0,0,0.2); width: 400px; }
        .signup-container h2 { text-align: center; margin-bottom: 20px; color: #333; }
        .signup-container label { display: block; margin-bottom: 5px; font-weight: bold; }
        .signup-container input[type="text"], .signup-container input[type="password"], .signup-container input[type="email"], .signup-container select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; }
        .signup-container input[type="submit"] { width: 100%; padding: 10px; background: #4CAF50; border: none; color: #fff; font-weight: bold; border-radius: 5px; cursor: pointer; }
        .signup-container input[type="submit"]:hover { background: #45a049; }
        .error { color: red; text-align: center; margin-bottom: 10px; }
        .success { color: green; text-align: center; margin-bottom: 10px; }
        .signup-container a { text-decoration: none; color: #4CAF50; }
        .signup-container a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="header">
    <h1>ISDN Sales Distribution System</h1>
</div>

<div class="signup-container">
    <h2>Sign Up</h2>

    <?php
    if(isset($_SESSION['error'])){ echo "<div class='error'>".$_SESSION['error']."</div>"; unset($_SESSION['error']); }
    if(isset($_SESSION['success'])){ echo "<div class='success'>".$_SESSION['success']."</div>"; unset($_SESSION['success']); }
    ?>

    <form action="" method="post">
        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Role:</label>
        <select name="role" id="role" onchange="toggleFields()" required>
            <option value="">--Select Role--</option>
            <option value="Customer">Customer</option>
            <option value="Delivery_Person">Delivery Person</option>
            <option value="RDC_Staff">RDC Staff</option>
            <option value="HO_Admin">HO Admin</option>
        </select>

        <div id="branch_div" style="display:none;">
            <label>Branch:</label>
            <select name="branch">
                <option value="">--Select Branch--</option>
                <option value="Colombo">Colombo</option>
                <option value="Kandy">Kandy</option>
                <option value="Galle">Galle</option>
            </select>
        </div>

        <div id="customer_fields" style="display:none;">
            <label>Phone:</label>
            <input type="text" name="phone">

            <label>Address:</label>
            <input type="text" name="address">
        </div>

        <div id="delivery_fields" style="display:none;">
            <label>Phone:</label>
            <input type="text" name="phone">
        </div>

        <input type="submit" name="register" value="Sign Up">
    </form>

    <p style="margin-top: 15px; text-align:center;">
        Already have an account? <a href="login.php">Login</a>
    </p>
</div>

<script>
function toggleFields(){
    var role = document.getElementById('role').value;
    document.getElementById('branch_div').style.display = (role=='Customer' || role=='RDC_Staff') ? 'block' : 'none';
    document.getElementById('customer_fields').style.display = (role=='Customer') ? 'block' : 'none';
    document.getElementById('delivery_fields').style.display = (role=='Delivery_Person') ? 'block' : 'none';
}
</script>

</body>
</html>
