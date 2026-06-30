<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("admin/inc/config.php");

$res = mysqli_query($con, 'SELECT * FROM tbl_coupon');
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
?>
