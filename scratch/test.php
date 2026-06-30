<?php
require 'd:/xampp/htdocs/araweb/vps-secondsight_health/admin/inc/config.php';
$res = mysqli_query($con, 'SELECT * FROM tbl_user_coupon LIMIT 5');
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
?>
