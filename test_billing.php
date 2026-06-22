<?php require 'admin/inc/config.php'; $res = mysqli_query($con, 'SELECT * FROM tbl_billing_address WHERE user_id = \'84760\''); while($r = mysqli_fetch_assoc($res)) print_r($r); ?>
