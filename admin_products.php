<?php
session_start();
include 'db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'HO_Admin'){
    header("Location: login.php");
    exit();
}

if(isset($_POST['add_product'])){
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock_quantity'];

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock_quantity) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $name, $description, $price, $stock);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_products.php");
    exit();
}

if(isset($_GET['delete_id'])){
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_products.php");
    exit();
}

if(isset($_POST['update_product'])){
    $id = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock_quantity'];

    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock_quantity=? WHERE product_id=?");
    $stmt->bind_param("ssdii", $name, $description, $price, $stock, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_products.php");
    exit();
}

$result = $conn->query("SELECT * FROM products ORDER BY product_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Products</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        h2 { color: #4CAF50; }
        table { border-collapse: collapse; width: 100%; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table th, table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        table th { background: #4CAF50; color: #fff; }
        input[type=text], input[type=number] { width: 100%; padding: 6px; margin: 4px 0; }
        input[type=submit], button { padding: 6px 12px; background: #4CAF50; color: #fff; border: none; cursor: pointer; border-radius: 4px; }
        input[type=submit]:hover, button:hover { background: #388e3c; }
        .form-container { background: #fff; padding: 15px; margin-bottom: 20px; border-radius: 6px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<h2>Admin - Manage Products</h2>

<div class="form-container">
    <h3>Add New Product</h3>
    <form method="post">
        <input type="text" name="name" placeholder="Product Name" required>
        <input type="text" name="description" placeholder="Description" required>
        <input type="number" step="0.01" name="price" placeholder="Price" required>
        <input type="number" name="stock_quantity" placeholder="Stock Quantity" required>
        <input type="submit" name="add_product" value="Add Product">
    </form>
</div>

<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Description</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Actions</th>
    </tr>

    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <form method="post">
        <td><?php echo $row['product_id']; ?>
            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
        </td>
        <td><input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>"></td>
        <td><input type="text" name="description" value="<?php echo htmlspecialchars($row['description']); ?>"></td>
        <td><input type="number" step="0.01" name="price" value="<?php echo $row['price']; ?>"></td>
        <td><input type="number" name="stock_quantity" value="<?php echo $row['stock_quantity']; ?>"></td>
        <td>
            <input type="submit" name="update_product" value="Update">
            <a href="admin_products.php?delete_id=<?php echo $row['product_id']; ?>" onclick="return confirm('Are you sure?');">
                <button type="button">Delete</button>
            </a>
        </td>
        </form>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
