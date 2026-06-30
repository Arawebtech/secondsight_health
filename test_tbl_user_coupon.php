<?php
include("admin/inc/config.php");
$res = mysqli_query($con, "DESCRIBE tbl_user_coupon");
while($r = mysqli_fetch_assoc($res)) {
    echo $r['Field'] . " ";
}
?>
