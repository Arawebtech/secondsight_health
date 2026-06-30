<?php
include("admin/include/db_config.php");
$res = mysqli_query($conn, "DESCRIBE user");
while($r = mysqli_fetch_assoc($res)) {
    echo $r['Field'] . " ";
}
?>
