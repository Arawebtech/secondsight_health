<?php
$servername = "localhost";
$username = "root";
$password = "";
$conn = mysqli_connect($servername, $username, $password, "secondside_seller");
$res = mysqli_query($conn, "SELECT id, user_name, customer_id FROM user WHERE id=256 OR customer_id=256 LIMIT 5");
echo "Seller DB:\n";
while($row = mysqli_fetch_assoc($res)) print_r($row);

$conn2 = mysqli_connect($servername, $username, $password, "new_jhbewdmy_ssf_in");
$res2 = mysqli_query($conn2, "SELECT id, full_name, email FROM tbl_user WHERE id=256 LIMIT 5");
echo "\nSSF DB (Customer 256):\n";
while($row = mysqli_fetch_assoc($res2)) print_r($row);
?>
