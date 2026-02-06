<?php
session_start();
include 'db.php';

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if(empty($username) || empty($password)){
        $_SESSION['login_error'] = "Please fill all fields.";
        header("Location: login.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT user_id, username, password, role, branch FROM users WHERE username = ?");
    if(!$stmt) die("SQL Prepare Error: " . $conn->error);

    $stmt->bind_param("s", $username);
    if(!$stmt->execute()) die("SQL Execute Error: " . $stmt->error);

    $stmt->store_result();

    if($stmt->num_rows === 1){
        $stmt->bind_result($user_id, $db_username, $db_password, $role_from_db, $branch_from_db);
        $stmt->fetch();

        if(password_verify($password, $db_password)){

            $role_lower = strtolower(trim($role_from_db));
            switch($role_lower){
                case 'customer':
                    $role = 'Customer';
                    break;
                case 'delivery person':
                case 'delivery_person':
                case 'delivery':
                    $role = 'Delivery_Person';
                    break;
                case 'rdc staff':
                case 'rdc_staff':
                    $role = 'RDC_Staff';
                    break;
                case 'ho admin':
                case 'ho_admin':
                    $role = 'HO_Admin';
                    break;
                default:
                    $role = $role_from_db;
            }

            $_SESSION['user_id']  = $user_id;
            $_SESSION['username'] = $db_username;
            $_SESSION['role']     = $role;
            $_SESSION['branch']   = $branch_from_db;

            if($role === 'Delivery_Person'){
                $dp_check = $conn->query("SELECT delivery_person_id FROM delivery_person WHERE user_id = $user_id");
                if($dp_check && $dp_check->num_rows > 0){
                    $dp = $dp_check->fetch_assoc();
                    $_SESSION['delivery_person_id'] = $dp['delivery_person_id'];
                } else {
                    $stmt_dp = $conn->prepare("INSERT INTO delivery_person (user_id) VALUES (?)");
                    if(!$stmt_dp) die("Delivery Person Insert Prepare Error: " . $conn->error);
                    $stmt_dp->bind_param("i", $user_id);
                    if(!$stmt_dp->execute()) die("Delivery Person Insert Execute Error: " . $stmt_dp->error);
                    $_SESSION['delivery_person_id'] = $stmt_dp->insert_id;
                    $stmt_dp->close();
                }

                $order_result = $conn->query("SELECT order_id FROM delivery WHERE delivery_person_id = " . $_SESSION['delivery_person_id'] . " LIMIT 1");
                if($order_result && $order_result->num_rows > 0){
                    $order = $order_result->fetch_assoc();
                    $_SESSION['current_order_id'] = $order['order_id'];
                } else {
                    $_SESSION['current_order_id'] = 0; 
                }
            }

            if($role === 'Customer'){
                $check = $conn->query("SELECT customer_id FROM customers WHERE user_id = $user_id");
                if(!$check) die("Customer Select Error: " . $conn->error);

                if($check->num_rows == 0){
                    $stmt2 = $conn->prepare("INSERT INTO customers (user_id) VALUES (?)");
                    if(!$stmt2) die("Insert Customer Prepare Error: " . $conn->error);
                    $stmt2->bind_param("i", $user_id);
                    if(!$stmt2->execute()) die("Insert Customer Execute Error: " . $stmt2->error);
                    $stmt2->close();
                }

                $customer_result = $conn->query("SELECT customer_id FROM customers WHERE user_id = $user_id");
                if($customer_result && $customer_result->num_rows > 0){
                    $customer = $customer_result->fetch_assoc();
                    $_SESSION['customer_id'] = $customer['customer_id'];
                } else {
                    die("Failed to retrieve customer record.");
                }
            }

            header("Location: dashboard.php");
            exit();

        } else {
            $_SESSION['login_error'] = "Invalid password!";
            header("Location: login.php");
            exit();
        }

    } else {
        $_SESSION['login_error'] = "Invalid username!";
        header("Location: login.php");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>
