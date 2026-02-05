<?php
session_start();
include 'db.php';

// Only HO_Admin can access
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'HO_Admin'){
    header("Location: login.php");
    exit();
}

// --- Handle Add User ---
if(isset($_POST['add_user'])){
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_users.php");
    exit();
}

// --- Handle Update User ---
if(isset($_POST['update_user'])){
    $id = $_POST['user_id'];
    $username = $_POST['username'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET username=?, role=? WHERE user_id=?");
    $stmt->bind_param("ssi", $username, $role, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_users.php");
    exit();
}

// --- Handle Delete User ---
if(isset($_GET['delete_id'])){
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_users.php");
    exit();
}

// Fetch all users
$result = $conn->query("SELECT user_id, username, role FROM users ORDER BY user_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Users</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        h2 { color: #4CAF50; }
        table { border-collapse: collapse; width: 100%; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table th, table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        table th { background: #4CAF50; color: #fff; }
        input[type=text], input[type=password], select { width: 100%; padding: 6px; margin: 4px 0; }
        input[type=submit], button { padding: 6px 12px; background: #4CAF50; color: #fff; border: none; cursor: pointer; border-radius: 4px; }
        input[type=submit]:hover, button:hover { background: #388e3c; }
        .form-container { background: #fff; padding: 15px; margin-bottom: 20px; border-radius: 6px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<h2>Admin - Manage Users</h2>

<div class="form-container">
    <h3>Add New User</h3>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="Delivery_Person">Delivery Person</option>
            <option value="Customer">Customer</option>
            <option value="RDC_Staff">RDC Staff</option>
            <option value="HO_Admin">HO Admin</option>
        </select>
        <input type="submit" name="add_user" value="Add User">
    </form>
</div>

<table>
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Role</th>
        <th>Actions</th>
    </tr>

    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <form method="post">
            <td>
                <?php echo $row['user_id']; ?>
                <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
            </td>
            <td><input type="text" name="username" value="<?php echo htmlspecialchars($row['username']); ?>"></td>
            <td>
                <select name="role">
                    <option value="Customer" <?php if($row['role']=='Customer') echo 'selected'; ?>>Customer</option>
                    <option value="Delivery_Person" <?php if($row['role']=='Delivery_Person') echo 'selected'; ?>>Delivery Person</option>
                    <option value="RDC_Staff" <?php if($row['role']=='RDC_Staff') echo 'selected'; ?>>RDC Staff</option>
                    <option value="HO_Admin" <?php if($row['role']=='HO_Admin') echo 'selected'; ?>>HO Admin</option>
                </select>
            </td>
            <td>
                <input type="submit" name="update_user" value="Update">
                <a href="admin_users.php?delete_id=<?php echo $row['user_id']; ?>" onclick="return confirm('Are you sure?');">
                    <button type="button">Delete</button>
                </a>
            </td>
        </form>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
