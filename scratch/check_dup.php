<?php
require 'admin/inc/config.php';
$res = mysqli_query($con, 'SELECT user_id, COUNT(*) FROM tbl_user_coupon GROUP BY user_id HAVING COUNT(*) > 1');
echo mysqli_num_rows($res);
?>
