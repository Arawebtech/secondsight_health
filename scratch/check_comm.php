<?php
require 'admin/inc/config.php';
$res = mysqli_query($con, 'SELECT * FROM tbl_affiliate_commission LIMIT 5');
while ($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
?>
