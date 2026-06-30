<?php
include("admin/inc/config.php");
$res = mysqli_query($con, "SELECT * FROM tbl_user_coupon WHERE user_id = '325'");
while($r = mysqli_fetch_assoc($res)) {
    print_r($r);
}
?>
