<?php
session_start();
include("../admin/inc/config.php");
header('Content-Type: application/json');

$response = ['success' => false];

// Don't auto-apply if a coupon is already applied or manually removed recently
if (isset($_SESSION['coupon'])) {
    echo json_encode($response);
    exit;
}

// Allow auto-apply even if previously removed, to ensure it forcefully applies as requested.

$user_id = $_SESSION['user_id'] ?? $_SESSION['temp_user_id'] ?? null;
if (!$user_id) {
    echo json_encode($response);
    exit;
}

$q_cart = mysqli_query($con, "SELECT p_id, p_actual_price, no_of_item FROM tbl_cart WHERE user_id = '$user_id' AND is_ordered = '0'");
if ($q_cart && mysqli_num_rows($q_cart) > 0) {
    while ($c_item = mysqli_fetch_assoc($q_cart)) {
        $item_p_id = $c_item['p_id'];
        
        $q_coupon = mysqli_query($con, "SELECT * FROM tbl_coupon WHERE p_id = '$item_p_id' AND user_id = '0' ORDER BY id DESC LIMIT 1");
        if ($q_coupon && mysqli_num_rows($q_coupon) > 0) {
            $c_data = mysqli_fetch_assoc($q_coupon);
            $coupon_code = $c_data['coupon_code'];
            
            // Apply it via the same logic as apply-coupon.php by pretending it's a POST
            $_POST['coupon_code'] = $coupon_code;
            
            // Instead of including apply-coupon.php directly (which echoes and exits), we do it manually:
            $coupon_amount = (float)$c_data['amount'];
            $coupon_type = $c_data['type'];
            
            $eligible_total = (float)$c_item['p_actual_price'] * (int)$c_item['no_of_item'];
            if ($eligible_total > 0) {
                $discount_amt = 0;
                if ($coupon_type === 'percent') {
                    $discount_amt = min($eligible_total, ($eligible_total * $coupon_amount / 100));
                } else {
                    $discount_amt = min($eligible_total, $coupon_amount);
                }
                
                $_SESSION['coupon'] = [
                    'code'   => $coupon_code,
                    'amount' => $discount_amt,
                    'p_id'   => (int)$c_data['p_id'],
                    'type'   => $coupon_type
                ];
                
                $response['success'] = true;
                $response['code'] = $coupon_code;
                echo json_encode($response);
                exit;
            }
        }
    }
}

echo json_encode($response);
?>
