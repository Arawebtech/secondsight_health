<?php
require 'admin/inc/config.php';
$res = mysqli_query($con, 'SHOW TABLES');
while($r = mysqli_fetch_array($res)) {
    echo $r[0] . "\n";
}
?>
