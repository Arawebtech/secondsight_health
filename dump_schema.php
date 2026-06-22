<?php require 'admin/inc/config.php'; $res = mysqli_query($con, 'SHOW COLUMNS FROM tbl_coupon'); while ($row = mysqli_fetch_assoc($res)) { print_r($row); } ?>
