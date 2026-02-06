<?php
include 'db.php'; 

if(isset($_POST['update_status'])){
    $delivery_id = $_POST['delivery_id']; 
    $new_status = $_POST['status'];   

    $stmt = $conn->prepare("UPDATE delivery SET status=? WHERE delivery_id=?");
    $stmt->bind_param("si", $new_status, $delivery_id);
    if($stmt->execute()){
        echo "<p>Status update wela thiyenawa!</p>";
    } else {
        echo "<p>Status update karanna bari una.</p>";
    }
}
?>

<table border="1">
<tr>
    <th>Delivery ID</th>
    <th>Order ID</th>
    <th>Status</th>
    <th>Update</th>
</tr>

<?php
$result = $conn->query("SELECT * FROM delivery");
while($row = $result->fetch_assoc()){
    ?>
    <tr>
        <td><?php echo $row['delivery_id']; ?></td>
        <td><?php echo $row['order_id']; ?></td>
        <td><?php echo $row['status']; ?></td>
        <td>
            <form method="POST">
                <input type="hidden" name="delivery_id" value="<?php echo $row['delivery_id']; ?>">
                <select name="status">
                    <option value="Pending" <?php if($row['status']=="Pending") echo "selected"; ?>>Pending</option>
                    <option value="On the way" <?php if($row['status']=="On the way") echo "selected"; ?>>On the way</option>
                    <option value="Delivered" <?php if($row['status']=="Delivered") echo "selected"; ?>>Delivered</option>
                </select>
                <button type="submit" name="update_status">Update Status</button>
            </form>
        </td>
    </tr>
    <?php
}
?>
</table>
