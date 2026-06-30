<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("admin/include/db_config.php");

$query = "SELECT customer_id FROM user WHERE id = '65'";
$result = mysqli_query($conn, $query);
$seller = mysqli_fetch_assoc($result);
print_r($seller);
?>
