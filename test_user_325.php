<?php
include("admin/inc/config.php");
$res = mysqli_query($con, "SELECT id, status FROM tbl_user WHERE id = '325'");
print_r(mysqli_fetch_assoc($res));
?>
