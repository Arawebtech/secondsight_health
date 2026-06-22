<?php require 'admin/inc/config.php'; $res = mysqli_query($con, 'SELECT * FROM tbl_payment ORDER BY id DESC LIMIT 2'); while($r = mysqli_fetch_assoc($res)) print_r($r); ?>
