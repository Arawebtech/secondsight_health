<?php
$ch = curl_init("http://localhost/araweb/vps-secondsight_health/ajax/auto-apply-generic.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
echo $res;
?>
