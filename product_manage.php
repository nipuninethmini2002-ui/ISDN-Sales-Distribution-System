<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id']) || ($_SESSION['role']!='RDC_Staff' && $_SESSION['role']!='HO_Admin')){
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
}

if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $conn->query("DELETE FROM products WHERE product_id='$id'");
}

$result = $conn->query("SELECT * FROM products");
?>

<h2>Manage Products</h2>

<form method="post">
    <input type="text" name="name" placeholder="Product Name" required>
    <input type="text" name="description" placeholder="Description">
    <input type="number" step="0.01" name="price" placeholder="Price" required>
    <input type="number" name="stock_quantity" placeholder="Stock" required>
    <input type="submit" name="add_product" value="Add Product">
</form>

<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>ID</th><th>Name</th><th>Description</th><th>Price</th><th>Stock</th><th>Action</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['product_id']; ?></td>
        <td><?php echo $row['name']; ?></td>
        <td><?php echo $row['description']; ?></td>
        <td><?php echo $row['price']; ?></td>
        <td><?php echo $row['stock_quantity']; ?></td>
        <td>
            <a href="product_manage.php?delete=<?php echo $row['products_id']; ?>" onclick="return confirm('Delete?')">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<a href="dashboard.php">Back to Dashboard</a>
