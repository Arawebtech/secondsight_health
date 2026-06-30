<?php
require 'd:/xampp/htdocs/araweb/vps-secondsight_health/admin/inc/config.php';
$res = mysqli_query($con, 'SELECT id, user_name, customer_id FROM user LIMIT 5');
echo "Sellers (user table):\n";
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}

$res2 = mysqli_query($con, 'SELECT id, full_name, email FROM tbl_user LIMIT 5');
echo "\nCustomers (tbl_user table):\n";
while($row = mysqli_fetch_assoc($res2)) {
    print_r($row);
}
?>
