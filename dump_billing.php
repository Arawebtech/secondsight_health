<?php require 'admin/inc/config.php'; $res = mysqli_query($con, 'SHOW COLUMNS FROM tbl_billing_address'); while ($r = mysqli_fetch_assoc($res)) print_r($r); ?>
