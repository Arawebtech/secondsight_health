<?php
require 'admin/inc/config.php';
$res = mysqli_query($con, 'DESCRIBE tbl_payout_requests');
if (!$res) {
    echo "Table does not exist or error: " . mysqli_error($con);
} else {
    while($r = mysqli_fetch_array($res)) {
        echo $r[0] . " - " . $r[1] . "\n";
    }
}
?>
