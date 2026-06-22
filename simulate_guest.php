<?php
require 'admin/inc/config.php';
session_start();
$_SESSION['temp_user_id'] = 999999;
$_SESSION['is_guest_checkout'] = true;
$user_id = 999999;

mysqli_query($con, "DELETE FROM tbl_cart WHERE user_id = '$user_id'");
mysqli_query($con, "DELETE FROM tbl_billing_address WHERE user_id = '$user_id'");
mysqli_query($con, "DELETE FROM tbl_shipping_address WHERE user_id = '$user_id'");

mysqli_query($con, "INSERT INTO tbl_cart (user_id, p_id, p_name, p_price, p_actual_price, p_gst, p_quantity, no_of_item, is_ordered) VALUES ('$user_id', 354, 'Test', 100, 100, 5, 1, 1, '0')");
mysqli_query($con, "INSERT INTO tbl_billing_address (user_id, name, phone_no, email, building_no, street_address, landmark, town, district, state, pincode) VALUES ('$user_id', 'Test', '1234567890', 'test@test.com', 'A1', 'Street', 'Land', 'Town', 'Dist', 'State', 123456)");
mysqli_query($con, "INSERT INTO tbl_shipping_address (user_id, name, phone_no, email, building_no, street_address, landmark, town, district, state, pincode) VALUES ('$user_id', 'Test', '1234567890', 'test@test.com', 'A1', 'Street', 'Land', 'Town', 'Dist', 'State', 123456)");

$_POST['checkout_place_order'] = true;
$_POST['payment_method'] = 'cod';

ob_start();
include 'checkout.php';
$out = ob_get_clean();
if (strpos($out, 'Error placing order') !== false) {
    echo 'Error found in output: ' . substr($out, strpos($out, 'Error placing order'), 100);
} else {
    echo 'Output: ' . $out;
    echo 'Order placed successfully!';
}
?>
