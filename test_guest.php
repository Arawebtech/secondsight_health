<?php
require 'admin/inc/config.php';
\ = 999999;
\  = "SELECT * FROM tbl_cart WHERE user_id = '\' AND is_ordered = '0'";
\ = mysqli_query(\, \);
echo mysqli_num_rows(\);
?>
