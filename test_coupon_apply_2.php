<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("admin/inc/config.php");

echo "Initial Setup:\n";
// Setup a guest session
$_SESSION['temp_user_id'] = 99999;
unset($_SESSION['user_id']);
$_SESSION['product_ref'][356] = 256; // Seller referral

// Clear cart for temp user and real user
mysqli_query($con, "DELETE FROM tbl_cart WHERE user_id = '99999' OR user_id = '29'");

// Add item to cart as guest
mysqli_query($con, "INSERT INTO tbl_cart (user_id, p_id, p_actual_price, no_of_item, is_ordered) VALUES ('99999', 356, 100, 2, '0')");

echo "Guest Cart added.\n";

// Simulate Login
$_SESSION['user_id'] = 29;
$user_id = 29;

// Migrate cart
$temp_user_id = $_SESSION['temp_user_id'];
$_SESSION['temp_user_id'] = "";
$query_update = "UPDATE tbl_cart SET user_id = '$user_id' WHERE user_id = '$temp_user_id'";
mysqli_query($con, $query_update);

echo "Cart Migrated to user_id 29.\n";

// Simulate cart.php Auto-apply
$has_ref = isset($_SESSION['ref_user_id']) || !empty($_SESSION['product_ref']);
echo "has_ref: " . ($has_ref ? 'true' : 'false') . "\n";

if ($user_id && $has_ref && !isset($_SESSION['coupon_removed'])) {
    if ($con) {
        $q_cart_items = mysqli_query($con, "SELECT p_id, p_actual_price, no_of_item FROM tbl_cart WHERE user_id = '$user_id' AND is_ordered = '0'");
        echo "Found items: " . mysqli_num_rows($q_cart_items) . "\n";
        
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

                echo "Checking item $item_p_id with partner_id $partner_id\n";

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
                        echo "Coupon FOUND: " . $uc_data['coupon_code'] . "\n";
                    } else {
                        echo "Coupon NOT FOUND in tbl_user_coupon for partner $partner_id and item $item_p_id\n";
                    }
                }
            }
        }
    }
}
?>
