<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("admin/include/db_config.php");

$query = "SELECT buy_link FROM user WHERE id = '65'";
$result = mysqli_query($conn, $query);
$seller = mysqli_fetch_object($result);
echo "Buy link: " . $seller->buy_link;
?>
