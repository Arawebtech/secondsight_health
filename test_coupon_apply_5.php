<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("admin/inc/config.php");

echo "Test 5: Guest adds product -> logs in -> visits cart\n";
// Setup a guest session
$_SESSION['temp_user_id'] = 88888;
unset($_SESSION['user_id']);
unset($_SESSION['coupon']);
unset($_SESSION['coupon_removed']);
unset($_SESSION['ref_user_id']);
$_SESSION['product_ref'] = [];

// Clear cart for temp user and real user
mysqli_query($con, "DELETE FROM tbl_cart WHERE user_id = '88888' OR user_id = '29'");

// Add item to cart as guest (p_id 356 has a generic coupon)
mysqli_query($con, "INSERT INTO tbl_cart (user_id, p_id, p_actual_price, no_of_item, is_ordered) VALUES ('88888', 356, 100, 2, '0')");

echo "Guest Cart added.\n";

// Guest visits cart page (simulate cart.php logic)
$user_id = 88888;

// validation block
if (isset($_SESSION['coupon']) && is_array($_SESSION['coupon']) && $con) {
    echo "Validating session coupon...\n";
    // ...
} else {
    echo "No coupon to validate.\n";
}

// generic auto-apply block
if (!isset($_SESSION['coupon']) && !isset($_SESSION['coupon_removed'])) {
    if ($con) {
        $q_cart_items = mysqli_query($con, "SELECT p_id, p_actual_price, no_of_item FROM tbl_cart WHERE user_id = '$user_id' AND is_ordered = '0'");
        if ($q_cart_items && mysqli_num_rows($q_cart_items) > 0) {
            while ($c_item = mysqli_fetch_assoc($q_cart_items)) {
                $item_p_id = $c_item['p_id'];
                $q_coupon = mysqli_query($con, "SELECT * FROM tbl_coupon WHERE p_id = '$item_p_id' AND user_id = '0' ORDER BY id DESC LIMIT 1");
                if ($q_coupon && mysqli_num_rows($q_coupon) > 0) {
                    $c_data = mysqli_fetch_assoc($q_coupon);
                    echo "Guest Auto-applied generic coupon: " . $c_data['coupon_code'] . "\n";
                    $_SESSION['coupon'] = [
                        'code'   => $c_data['coupon_code'],
                        'amount' => 10,
                        'p_id'   => $c_data['p_id'],
                        'type'   => $c_data['type'],
                    ];
                    break;
                }
            }
        }
    }
}

echo "Simulate Login...\n";
// Simulate Login
$backup_coupon = isset($_SESSION['coupon']) ? $_SESSION['coupon'] : null;
$_SESSION['user_id'] = 29;
$user_id = 29;

if ($backup_coupon !== null) {
    $_SESSION['coupon'] = $backup_coupon;
    $_COOKIE['backup_coupon_data'] = json_encode($backup_coupon);
    echo "Backed up coupon: " . $backup_coupon['code'] . "\n";
}

// Migrate cart
$temp_user_id = $_SESSION['temp_user_id'];
$_SESSION['temp_user_id'] = "";
$query_update = "UPDATE tbl_cart SET user_id = '$user_id' WHERE user_id = '$temp_user_id'";
mysqli_query($con, $query_update);

echo "Logged-in user visits cart.php\n";

// Restore backup cookie
if (isset($_COOKIE['backup_coupon_data']) && !empty($_COOKIE['backup_coupon_data'])) {
    $decoded_coupon = json_decode($_COOKIE['backup_coupon_data'], true);
    if (is_array($decoded_coupon) && !empty($decoded_coupon['code'])) {
        $_SESSION['coupon'] = $decoded_coupon;
        echo "Restored from cookie: " . $decoded_coupon['code'] . "\n";
    }
    unset($_COOKIE['backup_coupon_data']);
}

// validation block
if (isset($_SESSION['coupon']) && is_array($_SESSION['coupon']) && $con) {
    $coupon_p_id_check = isset($_SESSION['coupon']['p_id']) ? (int)$_SESSION['coupon']['p_id'] : 0;
    $eligible_total_check = 0;
    $q_cart_check = mysqli_query($con, "SELECT p_id, p_actual_price, no_of_item FROM tbl_cart WHERE user_id = '$user_id' AND is_ordered = '0'");
    if ($q_cart_check && mysqli_num_rows($q_cart_check) > 0) {
        while ($c_item = mysqli_fetch_assoc($q_cart_check)) {
            if ($coupon_p_id_check === 0 || (int)$c_item['p_id'] === $coupon_p_id_check) {
                $eligible_total_check += (float)$c_item['p_actual_price'] * (int)$c_item['no_of_item'];
            }
        }
    }
    if ($eligible_total_check <= 0) {
        unset($_SESSION['coupon']);
        echo "Validation FAILED, unset coupon.\n";
    } else {
        echo "Validation PASSED for coupon.\n";
    }
}

// generic auto-apply block
if (!isset($_SESSION['coupon']) && !isset($_SESSION['coupon_removed'])) {
    if ($con) {
        $q_cart_items = mysqli_query($con, "SELECT p_id, p_actual_price, no_of_item FROM tbl_cart WHERE user_id = '$user_id' AND is_ordered = '0'");
        if ($q_cart_items && mysqli_num_rows($q_cart_items) > 0) {
            while ($c_item = mysqli_fetch_assoc($q_cart_items)) {
                $item_p_id = $c_item['p_id'];
                $q_coupon = mysqli_query($con, "SELECT * FROM tbl_coupon WHERE p_id = '$item_p_id' AND user_id = '0' ORDER BY id DESC LIMIT 1");
                if ($q_coupon && mysqli_num_rows($q_coupon) > 0) {
                    $c_data = mysqli_fetch_assoc($q_coupon);
                    echo "Logged-in Auto-applied generic coupon: " . $c_data['coupon_code'] . "\n";
                    $_SESSION['coupon'] = [
                        'code'   => $c_data['coupon_code'],
                        'amount' => 10,
                        'p_id'   => $c_data['p_id'],
                        'type'   => $c_data['type'],
                    ];
                    break;
                }
            }
        }
    }
} else {
    echo "Generic auto-apply skipped because coupon is already set.\n";
}

if (isset($_SESSION['coupon'])) {
    echo "FINAL COUPON: " . $_SESSION['coupon']['code'] . "\n";
} else {
    echo "FINAL COUPON: NONE\n";
}
?>
