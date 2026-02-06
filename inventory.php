<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id']) || !isset($_SESSION['role'])){
    header("Location: login.php");
    exit();
}

$role = trim($_SESSION['role']);

if(isset($_POST['add_inventory']) && ($role === 'HO_Admin' || $role === 'RDC_Staff')){
    $quantity = $_POST['quantity'];

    $stmt = $conn->prepare(
        "INSERT INTO inventory (quantity, last_updated) VALUES (?, NOW())"
    );
    $stmt->bind_param("i", $quantity);
    $stmt->execute();
    $stmt->close();

    header("Location: inventory.php");
    exit();
}

if(isset($_POST['update_inventory']) && ($role === 'HO_Admin' || $role === 'RDC_Staff')){
    $id = $_POST['inventory_id'];
    $quantity = $_POST['quantity'];

    $stmt = $conn->prepare(
        "UPDATE inventory SET quantity=?, last_updated=NOW() WHERE inventory_id=?"
    );
    $stmt->bind_param("ii", $quantity, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: inventory.php");
    exit();
}

if(isset($_POST['delete_inventory']) && $role === 'HO_Admin'){
    $id = $_POST['inventory_id'];

    $stmt = $conn->prepare(
        "DELETE FROM inventory WHERE inventory_id=?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: inventory.php");
    exit();
}

$result = $conn->query("SELECT * FROM inventory ORDER BY last_updated DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Management</title>
    <style>
        body{
            margin:0;
            font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #e8f5e9, #c8e6c9);
            min-height:100vh;
        }
        .header{
            background:#4CAF50;
            color:#fff;
            padding:20px;
            text-align:center;
        }
        .container{
            width:90%;
            max-width:900px;
            margin:30px auto;
            background:#fff;
            padding:30px;
            border-radius:15px;
        }
        h2{
            text-align:center;
            color:#2e7d32;
        }
        table{
            width:100%;
            border-collapse:collapse;
        }
        th{
            background:#4CAF50;
            color:#fff;
            padding:12px;
        }
        td{
            padding:10px;
            text-align:center;
            border-bottom:1px solid #ddd;
        }
        .btn-update{
            background:#2196F3;
            color:#fff;
            border:none;
            padding:6px 10px;
            border-radius:6px;
        }
        .btn-delete{
            background:#e53935;
            color:#fff;
            border:none;
            padding:6px 10px;
            border-radius:6px;
        }
    </style>
</head>

<body>

<div class="header">
    <h1>ISDN Inventory Management</h1>
</div>

<div class="container">

<h2>Inventory Stock Control</h2>

<?php if($role === 'HO_Admin' || $role === 'RDC_Staff'){ ?>
<form method="post">
    <input type="number" name="quantity" required>
    <button type="submit" name="add_inventory">Add Inventory</button>
</form>
<?php } ?>

<br>

<table>
<tr>
    <th>ID</th>
    <th>Quantity</th>
    <th>Last Updated</th>
    <th>Update</th>
    <?php if($role === 'HO_Admin'){ ?>
        <th>Delete</th>
    <?php } ?>
</tr>

<?php while($row = $result->fetch_assoc()){ ?>
<tr>
    <td><?= $row['inventory_id']; ?></td>
    <td><?= $row['quantity']; ?></td>
    <td><?= $row['last_updated']; ?></td>

    <td>
        <?php if($role === 'HO_Admin' || $role === 'RDC_Staff'){ ?>
        <form method="post">
            <input type="hidden" name="inventory_id" value="<?= $row['inventory_id']; ?>">
            <input type="number" name="quantity" value="<?= $row['quantity']; ?>" required>
            <button class="btn-update" name="update_inventory">Update</button>
        </form>
        <?php } ?>
    </td>

    <?php if($role === 'HO_Admin'){ ?>
    <td>
        <form method="post">
            <input type="hidden" name="inventory_id" value="<?= $row['inventory_id']; ?>">
            <button class="btn-delete" name="delete_inventory"
                onclick="return confirm('Are you sure?')">Delete</button>
        </form>
    </td>
    <?php } ?>
</tr>
<?php } ?>
</table>

</div>

</body>
</html>
