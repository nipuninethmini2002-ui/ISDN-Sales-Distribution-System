<?php
session_start();
include 'db.php';

// Only RDC Staff
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'RDC_Staff'){
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

// Add Product
if(isset($_POST['add_submit'])){
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);

    if(empty($name) || empty($category) || empty($price) || empty($stock)){
        $error = "Please fill all fields.";
    } else {
        $sql = "INSERT INTO products (name, category, price, stock_quantity) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if(!$stmt) { $error = $conn->error; }
        else {
            $stmt->bind_param("ssdi", $name, $category, $price, $stock);
            if($stmt->execute()){ 
                $success = "Product added!";
                header("Location: rdc_manage.php");
                exit();
            } else { $error = $stmt->error; }
            $stmt->close();
        }
    }
}

// Edit Product
if(isset($_POST['edit_submit'])){
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);

    $sql = "UPDATE products SET name=?, category=?, price=?, stock_quantity=? WHERE product_id=?";
    $stmt = $conn->prepare($sql);
    if(!$stmt) { $error = $conn->error; }
    else {
        $stmt->bind_param("ssdii", $name, $category, $price, $stock, $id);
        if($stmt->execute()){ 
            $success = "Product updated!";
            header("Location: rdc_manage.php");
            exit();
        } else { $error = $stmt->error; }
        $stmt->close();
    }
}

// Delete Product
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $sql = "DELETE FROM products WHERE product_id=?";
    $stmt = $conn->prepare($sql);
    if($stmt){
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $success = "Product deleted!";
        header("Location: rdc_manage.php");
        exit();
    } else { $error = $conn->error; }
}

// Fetch product to edit
$edit_product = null;
if(isset($_GET['edit'])){
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_product = $result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Products</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #e6f2ff; padding: 20px; }
h2 { text-align: center; color: #333; margin-bottom: 20px; }
.error { color:red; font-weight:bold; margin-bottom:10px; }
.success { color:green; font-weight:bold; margin-bottom:10px; }

form { background: #fff; padding: 25px; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.15); max-width: 500px; margin: 0 auto 30px auto; }
form h3 { color: #4CAF50; margin-bottom: 15px; text-align:center; }
form label { display:block; margin-top:10px; font-weight:500; }
form input[type=text], form input[type=number] { width:100%; padding:10px; margin-top:5px; border:1px solid #ccc; border-radius:8px; }
form button { width:100%; padding:12px; margin-top:20px; background: linear-gradient(45deg,#4CAF50,#66BB6A); border:none; border-radius:10px; color:#fff; font-size:16px; cursor:pointer; transition:0.3s; }
form button:hover { background: linear-gradient(45deg,#388e3c,#43a047); }

table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
th, td { padding:12px 15px; text-align:left; }
th { background: linear-gradient(45deg,#4CAF50,#66BB6A); color:#fff; }
tr:nth-child(even){ background:#f2f2f2; }
td a button { padding:6px 12px; border:none; border-radius:8px; color:#fff; cursor:pointer; transition:0.3s; }
td a button:hover { opacity:0.8; }
td a:first-child button { background:#2196F3; } 
td a:last-child button { background:#f44336; } 
</style>
</head>
<body>

<h2>Manage Products</h2>

<?php if($error) echo "<p class='error'>$error</p>"; ?>
<?php if($success) echo "<p class='success'>$success</p>"; ?>

<form method="POST" action="">
    <?php if($edit_product): ?>
        <h3>Edit Product ID <?php echo $edit_product['product_id']; ?></h3>
        <input type="hidden" name="id" value="<?php echo $edit_product['product_id']; ?>">
    <?php else: ?>
        <h3>Add New Product</h3>
    <?php endif; ?>

    <label>Name:</label>
    <input type="text" name="name" required value="<?php echo $edit_product['name'] ?? ''; ?>">

    <label>Category:</label>
    <input type="text" name="category" required value="<?php echo $edit_product['category'] ?? ''; ?>">

    <label>Price:</label>
    <input type="number" step="0.01" name="price" required value="<?php echo $edit_product['price'] ?? ''; ?>">

    <label>Stock:</label>
    <input type="number" name="stock" required value="<?php echo $edit_product['stock_quantity'] ?? ''; ?>">

    <?php if($edit_product): ?>
        <button type="submit" name="edit_submit">Update Product</button>
        <a href="rdc_manage.php"><button type="button">Cancel</button></a>
    <?php else: ?>
        <button type="submit" name="add_submit">Add Product</button>
    <?php endif; ?>
</form>

<table>
<tr>
<th>Product ID</th>
<th>Name</th>
<th>Category</th>
<th>Price</th>
<th>Stock</th>
<th>Actions</th>
</tr>

<?php
$sql = "SELECT * FROM products ORDER BY product_id DESC"; // show all products
$result = $conn->query($sql);

while($row = $result->fetch_assoc()){
    echo "<tr>
            <td>".htmlspecialchars($row['product_id'])."</td>
            <td>".htmlspecialchars($row['name'])."</td>
            <td>".htmlspecialchars($row['category'])."</td>
            <td>".htmlspecialchars($row['price'])."</td>
            <td>".htmlspecialchars($row['stock_quantity'])."</td>
            <td>
                <a href='?edit=".$row['product_id']."'><button>Edit</button></a>
                <a href='?delete=".$row['product_id']."' onclick='return confirm(\"Are you sure?\");'><button>Delete</button></a>
            </td>
          </tr>";
}
?>
</table>

</body>
</html>
