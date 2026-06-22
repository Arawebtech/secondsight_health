<?php
$content = file_get_contents('checkout.php');

// 1. Init variables
$content = str_replace('$b_full_name = $b_contact_no = ', '$b_full_name = $b_contact_no = $b_email = ', $content);
$content = str_replace('$s_full_name = $s_contact_no = ', '$s_full_name = $s_contact_no = $s_email = ', $content);

// 2. Fetch from DB (Billing)
$content = str_replace("\$b_contact_no       = \$data_b['phone_no'];", "\$b_contact_no       = \$data_b['phone_no'];\n        \$b_email            = \$data_b['email'] ?? '';", $content);

// 3. Full billing address concatenation 1
$content = str_replace('"$b_full_name, $b_contact_no, $b_building_no', '"$b_full_name, $b_contact_no, $b_email, $b_building_no', $content);

// 4. Fetch from DB (Shipping)
$content = str_replace("\$s_contact_no       = \$data_s['phone_no'];", "\$s_contact_no       = \$data_s['phone_no'];\n        \$s_email            = \$data_s['email'] ?? '';", $content);

// 5. Full shipping address concatenation 1
$content = str_replace('"$s_full_name, $s_contact_no, $s_building_no', '"$s_full_name, $s_contact_no, $s_email, $s_building_no', $content);

// 6. POST billing
$content = str_replace("\$b_contact_no     = mysqli_real_escape_string(\$con, \$_POST['b_contact_no']);", "\$b_contact_no     = mysqli_real_escape_string(\$con, \$_POST['b_contact_no']);\n    \$b_email          = mysqli_real_escape_string(\$con, \$_POST['b_email']);", $content);

// 7. Full billing address concatenation 2
$content = str_replace("\$b_full_name . ', ' . \$b_contact_no . ', ' . \$b_building_no", "\$b_full_name . ', ' . \$b_contact_no . ', ' . \$b_email . ', ' . \$b_building_no", $content);

// 8. SQL Billing Update
$content = str_replace("phone_no = '\$b_contact_no',", "phone_no = '\$b_contact_no', email = '\$b_email',", $content);

// 9. SQL Billing Insert
$content = str_replace("phone_no, building_no", "phone_no, email, building_no", $content);
$content = str_replace("'\$b_contact_no', '\$b_building_no'", "'\$b_contact_no', '\$b_email', '\$b_building_no'", $content);

// 10. POST shipping
$content = str_replace("\$s_contact_no                      = mysqli_real_escape_string(\$con, \$_POST['s_contact_no']);", "\$s_contact_no                      = mysqli_real_escape_string(\$con, \$_POST['s_contact_no']);\n        \$s_email                           = mysqli_real_escape_string(\$con, \$_POST['s_email']);", $content);

// 11. Full shipping address concatenation 2
$content = str_replace("\$s_full_name . ', ' . \$s_contact_no . ', ' . \$s_building_no", "\$s_full_name . ', ' . \$s_contact_no . ', ' . \$s_email . ', ' . \$s_building_no", $content);

// 12. Copy billing to shipping
$content = str_replace("\$s_contact_no                      = \$b_contact_no;", "\$s_contact_no                      = \$b_contact_no;\n        \$s_email                           = \$b_email;", $content);

// 13. SQL Shipping Update
$content = str_replace("phone_no = '\$s_contact_no', building_no", "phone_no = '\$s_contact_no', email = '\$s_email', building_no", $content);

// 14. SQL Shipping Insert
$content = str_replace("'\$s_contact_no', '\$s_building_no'", "'\$s_contact_no', '\$s_email', '\$s_building_no'", $content);

// 15. Fetch order billing
$content = str_replace("\$b_phone    = mysqli_real_escape_string(\$con, \$data_b['phone_no']);", "\$b_phone    = mysqli_real_escape_string(\$con, \$data_b['phone_no']);\n    \$b_email    = mysqli_real_escape_string(\$con, \$data_b['email'] ?? '');", $content);

// 16. Fetch order shipping
$content = str_replace("\$s_phone    = mysqli_real_escape_string(\$con, \$data_s['phone_no']);", "\$s_phone    = mysqli_real_escape_string(\$con, \$data_s['phone_no']);\n    \$s_email    = mysqli_real_escape_string(\$con, \$data_s['email'] ?? '');", $content);

// 17. Full order addresses
$content = str_replace("\$b_name . ', ' . \$b_phone . ', ' . \$b_building", "\$b_name . ', ' . \$b_phone . ', ' . \$b_email . ', ' . \$b_building", $content);
$content = str_replace("\$s_name . ', ' . \$s_phone . ', ' . \$s_building", "\$s_name . ', ' . \$s_phone . ', ' . \$s_email . ', ' . \$s_building", $content);

// 18. Order SQL
$content = str_replace("b_phone, b_building", "b_phone, b_email, b_building", $content);
$content = str_replace("s_phone, s_building", "s_phone, s_email, s_building", $content);
$content = str_replace("'\$b_phone', '\$b_building", "'\$b_phone', '\$b_email', '\$b_building", $content);
$content = str_replace("'\$s_phone', '\$s_building", "'\$s_phone', '\$s_email', '\$s_building", $content);

// Also add HTML input fields using another script or regex, actually we can do it via multi_replace_file_content!

file_put_contents('checkout.php', $content);
echo 'Done';
?>
