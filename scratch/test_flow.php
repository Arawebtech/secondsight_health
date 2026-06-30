<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'd:/xampp/htdocs/araweb/vps-secondsight_health/admin/inc/config.php';

// 1. Visit ?ref=256
$_GET['ref'] = '256';
if (isset($_GET['ref'])) {
    unset($_SESSION['coupon_removed']);
    $ref_val = $_GET['ref'];
    $stmt_ref = $pdo->prepare("SELECT id FROM tbl_user WHERE id = ? AND status = 'Active' LIMIT 1");
    $stmt_ref->execute([intval($ref_val)]);
    if ($stmt_ref->rowCount() > 0) {
        $p_id_ref = $stmt_ref->fetchColumn();
        $_SESSION['ref_user_id'] = $p_id_ref;
    }
}
echo "After ref visit: ref_user_id = " . ($_SESSION['ref_user_id'] ?? 'none') . "\n";

// 2. Login
$_SESSION['user_id'] = 256; // login as the user themselves, or someone else. Let's say user 256.
echo "After login: ref_user_id = " . ($_SESSION['ref_user_id'] ?? 'none') . "\n";

// 3. Add to cart
mysqli_query($con, "DELETE FROM tbl_cart WHERE user_id='256'");
mysqli_query($con, "INSERT INTO tbl_cart(user_id, p_id, p_name, p_price, p_actual_price, p_quantity, no_of_item, is_ordered) VALUES('256', '12', 'Test', 100, 100, '1', '1', '0')");

// 4. Load cart.php logic
$user_id = 256;
if ($user_id && !isset($_SESSION['coupon']) && !isset($_SESSION['coupon_removed'])) {
    $q_cart_items = mysqli_query($con, "SELECT p_id, p_actual_price, no_of_item FROM tbl_cart WHERE user_id = '$user_id' AND is_ordered = '0'");
    if ($q_cart_items && mysqli_num_rows($q_cart_items) > 0) {
        $cart_items = [];
        while ($c_row = mysqli_fetch_assoc($q_cart_items)) $cart_items[] = $c_row;
        foreach ($cart_items as $item) {
            $item_p_id = $item['p_id'];
            $partner_id = $_SESSION['ref_user_id'] ?? 0;
            echo "Checking coupon for partner $partner_id and item $item_p_id\n";
            if ($partner_id > 0) {
                $stmt_cp = $pdo->prepare("
                    SELECT uc.*, c.coupon_code, c.amount as coupon_amount, c.type as coupon_type, c.p_id as coupon_p_id
                    FROM tbl_user_coupon uc
                    JOIN tbl_coupon c ON uc.coupon_id = c.id
                    WHERE uc.user_id = ? AND (uc.p_id = ? OR uc.p_id IS NULL OR uc.p_id = 0)
                    LIMIT 1
                ");
                $stmt_cp->execute([$partner_id, $item_p_id]);
                $uc_data = $stmt_cp->fetch(PDO::FETCH_ASSOC);
                if ($uc_data) {
                    echo "Coupon applied: " . $uc_data['coupon_code'] . "\n";
                } else {
                    echo "No coupon found in tbl_user_coupon for partner $partner_id\n";
                }
            }
        }
    }
}
?>
