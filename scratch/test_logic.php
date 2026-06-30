<?php
session_start();
require 'd:/xampp/htdocs/araweb/vps-secondsight_health/admin/inc/config.php';

// Force login and cart item
$_SESSION['user_id'] = 256;
$_SESSION['ref_user_id'] = 256;
mysqli_query($con, "DELETE FROM tbl_cart WHERE user_id='256'");
mysqli_query($con, "INSERT INTO tbl_cart(user_id, p_id, p_name, p_price, p_actual_price, p_quantity, no_of_item, is_ordered) VALUES('256', '12', 'Test', 100, 100, '1', '1', '0')");

unset($_SESSION['coupon']);

$user_id = 256;
if ($user_id && !isset($_SESSION['coupon']) && !isset($_SESSION['coupon_removed'])) {
    $q_cart_items = mysqli_query($con, "SELECT p_id, p_actual_price, no_of_item FROM tbl_cart WHERE user_id = '$user_id' AND is_ordered = '0'");
    if ($q_cart_items && mysqli_num_rows($q_cart_items) > 0) {
        $cart_items = [];
        while ($c_row = mysqli_fetch_assoc($q_cart_items)) {
            $cart_items[] = $c_row;
        }

        foreach ($cart_items as $item) {
            $item_p_id  = $item['p_id'];
            $partner_id = 0;

            if (isset($_SESSION['product_ref'][$item_p_id])) {
                $partner_id = $_SESSION['product_ref'][$item_p_id];
            } elseif (isset($_SESSION['ref_user_id'])) {
                $partner_id = $_SESSION['ref_user_id'];
            }

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
                    echo "Coupon found: " . $uc_data['coupon_code'] . "\n";
                    $_SESSION['coupon'] = [
                        'code'   => $uc_data['coupon_code'],
                        'amount' => 50,
                        'p_id'   => $uc_data['coupon_p_id'],
                        'type'   => $uc_data['coupon_type'],
                    ];
                    break;
                }
            }
        }
    }
}

echo "Final session coupon: " . (isset($_SESSION['coupon']) ? $_SESSION['coupon']['code'] : 'NONE') . "\n";
?>
