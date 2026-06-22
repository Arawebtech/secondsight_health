<?php require 'admin/inc/config.php'; $res = mysqli_query($con, 'SELECT * FROM tbl_user WHERE id = 303'); print_r(mysqli_fetch_assoc($res)); ?>
