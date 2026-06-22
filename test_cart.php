<?php require 'admin/inc/config.php'; $res = mysqli_query($con, 'SELECT * FROM tbl_cart WHERE user_id = \'84760\''); while($r = mysqli_fetch_assoc($res)) print_r($r); ?>
