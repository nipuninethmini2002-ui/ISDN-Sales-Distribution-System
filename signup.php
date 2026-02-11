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

    $branch  = ($role=='Customer' || $role=='RDC_Staff') ? trim($_POST['branch'] ?? '') : null;
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if(empty($username) || empty($password) || empty($email) || empty($role)){
        $_SESSION['error'] = "All required fields must be filled!";
        header("Location: signup.php"); exit();
    }

    if(($role=='Customer' || $role=='RDC_Staff') && empty($branch)){
        $_SESSION['error'] = "Please select a branch!";
        header("Location: signup.php"); exit();
    }

    if($role=='Customer' && (empty($phone) || empty($address))){
        $_SESSION['error'] = "Phone and Address are required for Customer!";
        header("Location: signup.php"); exit();
    }

    if($role=='Delivery_Person' && empty($phone)){
        $_SESSION['error'] = "Phone is required for Delivery Person!";
        header("Location: signup.php"); exit();
    }

    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT user_id FROM users WHERE username=?");
    $check->bind_param("s", $username);
    $check->execute();
    if($check->get_result()->num_rows > 0){
        $_SESSION['error'] = "Username already exists!";
        header("Location: signup.php"); exit();
    }

    $stmt = $conn->prepare(
        "INSERT INTO users (username,password,email,role,branch)
         VALUES (?,?,?,?,?)"
    );
    $stmt->bind_param("sssss", $username, $password_hashed, $email, $role, $branch);

    if($stmt->execute()){
        $new_user_id = $stmt->insert_id;

        if($role=='Customer'){
            $stmt2 = $conn->prepare(
                "INSERT INTO customers (user_id, customer_branch, customer_phone, address)
                 VALUES (?,?,?,?)"
            );
            $stmt2->bind_param("isss", $new_user_id, $branch, $phone, $address);
            $stmt2->execute();
            $stmt2->close();
        }

        if($role=='Delivery_Person'){
            $vehicle_type = null;
            $status = 'Active';
            $created_at = date("Y-m-d H:i:s");

            $stmt2 = $conn->prepare(
                "INSERT INTO delivery_person (user_id, phone, vehicle_type, status, created_at)
                 VALUES (?,?,?,?,?)"
            );
            $stmt2->bind_param("issss", $new_user_id, $phone, $vehicle_type, $status, $created_at);
            $stmt2->execute();
            $stmt2->close();
        }

        if(!empty($email)){
            $mail = new PHPMailer(true);
            try{
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'isdnsystem@gmail.com';
                $mail->Password   = 'webnqrkahihtfato';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('isdnsystem@gmail.com', 'ISDN System');
                $mail->addAddress($email, $username);

                $mail->isHTML(false);
                $mail->Subject = 'Welcome to ISDN Sales Distribution System';
                $mail->Body =
                    "Hello $username,\n\n".
                    "Your account has been successfully created.\n\n".
                    "Role: $role\n".
                    "Branch: ".($branch ?? 'N/A')."\n\n".
                    "You can now login using your credentials.\n\n".
                    "Thank you,\nISDN System";

                $mail->send();
            }catch(Exception $e){
                error_log($mail->ErrorInfo);
            }
        }

        $_SESSION['success'] = "Account created successfully! Please login.";
        header("Location: login.php"); exit();
    }

    $_SESSION['error'] = "Registration failed!";
    header("Location: signup.php"); exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>ISDN Sign Up</title>
<style>
body{font-family:Arial;background:#94bda5;display:flex;flex-direction:column;align-items:center;padding:20px}
.header{background:#4CAF50;color:#fff;width:100%;text-align:center;padding:20px;border-radius:10px;margin-bottom:30px}
.signup-container{background:#fff;padding:30px 40px;border-radius:10px;width:400px}
label{font-weight:bold}
input,select{width:100%;padding:10px;margin-bottom:15px}
input[type=submit]{background:#4CAF50;color:#fff;border:none;cursor:pointer}
.error{color:red;text-align:center}
.success{color:green;text-align:center}
</style>
</head>

<body>

<div class="header"><h1>ISDN Sales Distribution System</h1></div>

<div class="signup-container">
<h2>Sign Up</h2>

<?php
if(isset($_SESSION['error'])){ echo "<div class='error'>".$_SESSION['error']."</div>"; unset($_SESSION['error']); }
if(isset($_SESSION['success'])){ echo "<div class='success'>".$_SESSION['success']."</div>"; unset($_SESSION['success']); }
?>

<form method="post">
<label>Username</label>
<input type="text" name="username" required>

<label>Password</label>
<input type="password" name="password" required>

<label>Email</label>
<input type="email" name="email" required>

<label>Role</label>
<select name="role" id="role" onchange="toggleFields()" required>
<option value="">--Select--</option>
<option value="Customer">Customer</option>
<option value="Delivery_Person">Delivery Person</option>
<option value="RDC_Staff">RDC Staff</option>
<option value="HO_Admin">HO Admin</option>
</select>

<div id="branch_div" style="display:none;">
<label>Branch</label>
<select name="branch">
<option value="">--Select--</option>
<option value="Colombo">Colombo</option>
<option value="Kandy">Kandy</option>
<option value="Galle">Galle</option>
</select>
</div>

<div id="phone_div" style="display:none;">
<label>Phone</label>
<input type="text" name="phone">
</div>

<div id="customer_fields" style="display:none;">
<label>Address</label>
<input type="text" name="address">
</div>

<input type="submit" name="register" value="Sign Up">
</form>
</div>

<script>
function toggleFields(){
    var r=document.getElementById('role').value;
    document.getElementById('branch_div').style.display =
        (r=='Customer'||r=='RDC_Staff')?'block':'none';
    document.getElementById('phone_div').style.display =
        (r=='Customer'||r=='Delivery_Person')?'block':'none';
    document.getElementById('customer_fields').style.display =
        (r=='Customer')?'block':'none';
}
</script>

</body>
</html>
