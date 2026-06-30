<?php
include("admin/inc/config.php");
session_start();

if (!function_exists('slugify')) {
    function slugify($string) {
        $slug = strtolower(trim($string));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}

$user_id = "";
// If session exists, use it
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_COOKIE['user_id'])) {
    // Restore session from cookie if available
    $cookie_user_id = intval($_COOKIE['user_id']);
    $query = "SELECT * FROM tbl_user WHERE id = '$cookie_user_id' AND status='Active' LIMIT 1";
    $result = mysqli_query($con, $query);
    if ($result && mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['email_id'] = $row['email'];
        $_SESSION['user_name'] = $row['full_name'];
        $_SESSION['phone'] = $row['phone'];

        $user_id = $row['id'];
    }
} elseif (isset($_SESSION['temp_user_id'])) {
    // Fallback to temp session if nothing else
    $user_id = $_SESSION['temp_user_id'];
}

// Restore referral from cookies if not in session
if (isset($_COOKIE['ref_user_id']) && !isset($_SESSION['ref_user_id'])) {
    $_SESSION['ref_user_id'] = intval($_COOKIE['ref_user_id']);
}

if (! isset($_SESSION['product_ref']) || ! is_array($_SESSION['product_ref'])) {
    $_SESSION['product_ref'] = [];
}
// Restore product-specific referrals from cookies
foreach ($_COOKIE as $key => $val) {
    if (strpos($key, 'prod_ref_') === 0) {
        $prod_id = intval(substr($key, 9));
        if (!isset($_SESSION['product_ref'][$prod_id])) {
            $_SESSION['product_ref'][$prod_id] = intval($val);
        }
    }
}

// Restore from cookie if session was lost during login redirect
if (isset($_COOKIE['backup_coupon_data']) && !empty($_COOKIE['backup_coupon_data'])) {
    $decoded_coupon = json_decode($_COOKIE['backup_coupon_data'], true);
    if (is_array($decoded_coupon) && !empty($decoded_coupon['code'])) {
        $_SESSION['coupon'] = $decoded_coupon;
    }
    setcookie("backup_coupon_data", "", time() - 3600, "/"); // Clear the cookie
}

// Validate existing session coupon against current cart
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
    
    // If the coupon doesn't apply to anything in the cart, unset it so auto-apply can try again
    if ($eligible_total_check <= 0) {
        unset($_SESSION['coupon']);
    }
}

// Default coupon values
$coupon_amount = 0;
$coupon_code = "";

// Auto-apply Referral Coupon if logged in and not yet applied
$has_ref = isset($_SESSION['ref_user_id']) || !empty($_SESSION['product_ref']);
if ($user_id && $has_ref && !isset($_SESSION['coupon_removed'])) {
    if ($con) {
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
                        $coupon_code   = $uc_data['coupon_code'];
                        $coupon_amount = (float) $uc_data['coupon_amount'];
                        $coupon_type   = $uc_data['coupon_type'];
                        $coupon_p_id   = (int) $uc_data['coupon_p_id'];

                        $eligible_total = 0.0;
                        foreach ($cart_items as $c_item) {
                            if ($coupon_p_id === 0 || (int) $c_item['p_id'] === $coupon_p_id) {
                                $eligible_total += (float) $c_item['p_actual_price'] * (int) $c_item['no_of_item'];
                            }
                        }

                        if ($eligible_total > 0) {
                            if ($coupon_type === 'percent') {
                                $discount_amt = min($eligible_total, ($eligible_total * $coupon_amount / 100));
                            } else {
                                $discount_amt = min($eligible_total, $coupon_amount);
                            }

                            $_SESSION['coupon'] = [
                                'code'   => $coupon_code,
                                'amount' => $discount_amt,
                                'p_id'   => $coupon_p_id,
                                'type'   => $coupon_type,
                            ];
                            break;
                        }
                    }
                }
            }
        }
    }
}

// Auto-apply Product-specific generic coupon if no coupon is applied
if (!isset($_SESSION['coupon'])) {
    if ($con) {
        $q_cart_items = mysqli_query($con, "SELECT p_id, p_actual_price, no_of_item FROM tbl_cart WHERE user_id = '$user_id' AND is_ordered = '0'");
        if ($q_cart_items && mysqli_num_rows($q_cart_items) > 0) {
            while ($c_item = mysqli_fetch_assoc($q_cart_items)) {
                $item_p_id = $c_item['p_id'];
                // Check if this product has a generic coupon
                $q_coupon = mysqli_query($con, "SELECT * FROM tbl_coupon WHERE p_id = '$item_p_id' AND user_id = '0' ORDER BY id DESC LIMIT 1");
                if ($q_coupon && mysqli_num_rows($q_coupon) > 0) {
                    $c_data = mysqli_fetch_assoc($q_coupon);
                    $coupon_code = $c_data['coupon_code'];
                    $coupon_amount = (float)$c_data['amount'];
                    $coupon_type = $c_data['type'];
                    $coupon_p_id = (int)$c_data['p_id'];

                    $eligible_total = (float)$c_item['p_actual_price'] * (int)$c_item['no_of_item'];

                    if ($eligible_total > 0) {
                        if ($coupon_type === 'percent') {
                            $discount_amt = min($eligible_total, ($eligible_total * $coupon_amount / 100));
                        } else {
                            $discount_amt = min($eligible_total, $coupon_amount);
                        }

                        $_SESSION['coupon'] = [
                            'code'   => $coupon_code,
                            'amount' => $discount_amt,
                            'p_id'   => $coupon_p_id,
                            'type'   => $coupon_type,
                        ];
                        break; // Applied one coupon, stop checking
                    }
                }
            }
        }
    }
}


// Use existing coupon session data if present
if (isset($_SESSION['coupon']) && is_array($_SESSION['coupon'])) {
    $coupon_code = $_SESSION['coupon']['code'] ?? '';
    $coupon_amount = $_SESSION['coupon']['amount'] ?? 0;
}
?>


<?php
include('include/header.php');
?>
<link rel="stylesheet" href="<?= $base_url; ?>assets/css/cart.css" type="text/css">


<?php
if (isset($_SESSION['flash_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show m-3" role="alert">'
        . $_SESSION['flash_message'] .
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    unset($_SESSION['flash_message']);
}
?>


<div class="cart-container" id="cart-page-container">
    <div class="container">
        <!-- Header -->
        <div class="cart-header">
            <h1><i class="fas fa-shopping-cart me-3"></i>Your Shopping Cart</h1>
            <p>Review your items and proceed to checkout</p>
        </div>
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
            <div class="cart-table">
                <div id="cart-items-container">
                    <?php
                    $query_cart = "SELECT * FROM tbl_cart WHERE user_id = '$user_id' AND is_ordered = '0'";
                    $result_cart = mysqli_query($con, $query_cart);
        
                    $count = 0;
                    $total = 0;
                    $gst = 0;
                    $sub_total = 0;
                    $shipping_charge = 0;
                    $p_total_weight = 0;
                    $btn_disable = "";
                    $has_items = false;
                    $eligible_total = 0;
        
                    while ($data_cart = mysqli_fetch_assoc($result_cart)) {
                        $has_items = true;
                        $p_id = $data_cart['p_id'];
                        $items_alert_msg = "";
                        $sku = $data_cart['sku'];
                        $query_stock = "SELECT in_stoke FROM tbl_product_price WHERE p_sku = '$sku'";
                        $result_stock = mysqli_query($con, $query_stock);
                        $stock_data = mysqli_fetch_assoc($result_stock);
                        $available_qty = $stock_data['in_stoke'];
        
                        $cart_qty = (int)$data_cart['no_of_item'];
        
                        // If cart quantity > stock, adjust it
                        if ($cart_qty > $available_qty) {
                            if ($available_qty > 0) {
                                $items_alert_msg = "Only $available_qty items left";
                                $cart_qty = $available_qty;
                                // Sync with DB so sidebar matches
                                mysqli_query($con, "UPDATE tbl_cart SET no_of_item = '$available_qty' WHERE id = '{$data_cart['id']}'");
                            } else {
                                $items_alert_msg = "Out of stock";
                                $btn_disable = "yes";
                                // Optionally remove from cart if out of stock
                                mysqli_query($con, "DELETE FROM tbl_cart WHERE id = '{$data_cart['id']}'");
                                continue; // skip rendering this item
                            }
                        }
                    
                        $p_base_total = $data_cart['p_price'] * $cart_qty;
                        $p_gst_total = $data_cart['p_gst'] * $cart_qty;
                        $p_actual_total = $p_base_total + $p_gst_total;

                        $sub_total += $p_base_total; // BASE total
                        $gst += $p_gst_total;        
                        $total += $p_actual_total;   

                        $coupon_p_id_check = isset($_SESSION['coupon']['p_id']) ? (int)$_SESSION['coupon']['p_id'] : 0;
                        if ($coupon_p_id_check === 0 || (int)$p_id === $coupon_p_id_check) {
                            $eligible_total += $p_actual_total;
                        }
                        ?>
                        <div class="cart-item" data-id="<?= $p_id; ?>" data-stock="<?= $available_qty; ?>" data-price="<?= $data_cart['p_price']; ?>" 
                            data-gst="<?= $data_cart['p_gst']; ?>" >
                           <a href="<?= $base_url; ?>product/<?= slugify($data_cart['p_name']); ?>">
                             <img src="<?= $base_url; ?>assets/img/product-detail/<?= $data_cart['p_image']; ?>" class="cart-item-img"
                                   style="width: 70px; height: 70px;">
                           </a>
                           <div class="cart-item-details">
                             <a href="<?= $base_url; ?>product/<?= slugify($data_cart['p_name']); ?>" class="text-decoration-none text-dark">
                                         <?= htmlspecialchars($data_cart['p_name']); ?>
                                     </a>
                        
                            <div class="d-flex align-items-center gap-2 my-1 justify-content-center justify-content-md-start">
                              <button class="btn btn-sm btn-outline-secondary qty-sub-cart" data-cartid="<?= $data_cart['id']; ?>">–</button>
                              <span id="qty-<?= $p_id; ?>"><?= $data_cart['no_of_item']; ?></span>
                              <button class="btn btn-sm btn-outline-secondary qty-add-cart" data-cartid="<?= $data_cart['id']; ?>">+</button>
                            </div>
                        
                            <div class="fw-bold text-dark" id="line-total-<?= $p_id; ?>">
                              ₹<?= $data_cart['p_price'] * $data_cart['no_of_item']; ?>
                            </div>
                            <div class="text-danger" id="stock-msg-<?= $p_id; ?>"><?= $items_alert_msg; ?></div>
                        
                            <a href="#" class="text-danger small remove-link-page" data-cartid="<?= $data_cart['id']; ?>">Remove</a>
                          </div>
                        </div>
        
                    <?php } ?>
        
                    <?php 
                    if (isset($_SESSION['coupon']) && is_array($_SESSION['coupon']) && $eligible_total > 0) {
                        $c_code = mysqli_real_escape_string($con, $_SESSION['coupon']['code']);
                        $c_query = "SELECT amount, type, p_id FROM tbl_coupon WHERE coupon_code = '$c_code' LIMIT 1";
                        $c_res = mysqli_query($con, $c_query);
                        if ($c_res && mysqli_num_rows($c_res) > 0) {
                            $c_row = mysqli_fetch_assoc($c_res);
                            $c_amount = (float)$c_row['amount'];
                            $c_type = $c_row['type'];
                            
                            if ($c_type === 'percent') {
                                $coupon_amount = min($eligible_total, ($eligible_total * $c_amount / 100));
                            } else {
                                $coupon_amount = min($eligible_total, $c_amount);
                            }
                            $_SESSION['coupon']['amount'] = $coupon_amount;
                            $_SESSION['coupon']['type'] = $c_type;
                            $_SESSION['coupon']['p_id'] = $c_row['p_id'];
                        }
                    }
                    ?>

                    <?php if (!$has_items): ?>
                        <div class="empty-cart text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted"></i>
                            <h3 class="mt-3">Your cart is empty</h3>
                            <p>Looks like you haven't added anything yet.</p>
                            <a href="<?= $base_url; ?>index.php" class="btn btn-cart secondary mt-3">
                                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

<!-- Order Summary -->
<div class="col-lg-4">
    <div class="cart-summary">
        <h3><i class="fas fa-calculator me-2"></i>Order Summary</h3>

        <!-- Coupon Section -->
        <div class="coupon-section mt-4">
            <h6><i class="fas fa-tag me-2"></i>Have a coupon?</h6>
            <div class="coupon-input d-flex gap-2">
                <input type="text" id="coupon-input" name="coupon_code" placeholder="Enter coupon code"
                    class="form-control" value="<?= htmlspecialchars($coupon_code); ?>">
                <button type="button" class="btn btn-primary" id="apply-coupon-btn" onclick="applyCoupon()">Apply</button>
            </div>
            <small id="coupon-message" class="text-success mt-2 d-block">
                <?php if (isset($_SESSION['coupon']) && is_array($_SESSION['coupon'])): ?>
                    Coupon applied: <?= htmlspecialchars($_SESSION['coupon']['code']) ?>
                <?php endif; ?>
            </small>
            <?php if (isset($_SESSION['coupon'])): ?>
                <button class="btn btn-link text-danger p-0 mt-2" onclick="removeCoupon()">Remove Coupon</button>
            <?php endif; ?>
        </div>

        <!-- Totals -->
        <div class="summary-row mt-4">
            <span>Subtotal:</span>
            <span id="subtotal">₹<?= number_format($sub_total, 2); ?></span>
        </div>
        <div class="summary-row">
            <span>GST:</span>
            <span id="gst">₹<?= number_format($gst, 2); ?></span>
        </div>
        <div class="summary-row">
            <span>Discount<?= $coupon_code ? " ($coupon_code)" : ''; ?>:</span>
            <span id="discount" class="text-success">-₹<?= number_format($coupon_amount, 2); ?></span>
        </div>
        <div class="summary-row final">
            <span>Total:</span>
            <span id="grand-total">₹<?= number_format($total - $coupon_amount, 2); ?></span>
        </div>
        
        <!-- Hidden fields to pass values to JS -->
        <input type="hidden" id="coupon-amount" value="<?= $coupon_amount; ?>">


        <?php 
        $is_logged_in = isset($_SESSION['user_id']);
        if ($btn_disable === "" && $user_id && $has_items): 
        ?>
            <?php if ($is_logged_in): ?>
                <a href="<?= $base_url; ?>checkout.php" class="btn btn-cart mt-3">Proceed to checkout</a>
            <?php else: ?>
                <button type="button" onclick="promptLoginOrGuest()" class="btn btn-cart mt-3" style="width: 100%;">Proceed to checkout</button>
            <?php endif; ?>
        <?php else: ?>
            <a href="<?= $base_url; ?>index.php" class="btn btn-cart secondary mt-3">
                <i class="fas fa-arrow-left me-2"></i>Continue Shopping
            </a>
        <?php endif; ?>

        <div class="security-badge mt-3 text-muted">
            <i class="fas fa-shield-alt me-2"></i>Secure Checkout - SSL Encrypted
        </div>
    </div>
</div>

        </div>
    </div>


    <script>
    function updateQuantity(cartId, action) {
        $.post("ajax/update-cart.php", { cart_id: cartId, action: action }, function(res) {
            location.reload();
        });
    }

    $(document).ready(function() {
        let currentCode = $('#coupon-input').val();
        
        // Auto-apply generic coupon via AJAX if none is applied
        if (!currentCode) {
            $.ajax({
                type: "POST",
                url: "ajax/auto-apply-generic.php",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }
        
        // Auto-restore coupon from localStorage if session dropped it
        let backupCode = localStorage.getItem('backup_coupon_code');
        
        if (!currentCode && backupCode) {
            console.log("Restoring coupon from localStorage:", backupCode);
            // We apply it automatically
            $.ajax({
                type: "POST",
                url: "ajax/apply-coupon.php",
                data: { coupon_code: backupCode },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        localStorage.removeItem('backup_coupon_code');
                    }
                }
            });
        }
    });

    // Apply Coupon
    function applyCoupon() {
        let couponCode = $('#coupon-input').val().trim();
        if (!couponCode) {
            alert("Please enter a coupon code");
            return;
        }
        
        $('#apply-coupon-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Applying...');

        $.ajax({
            type: "POST",
            url: "ajax/apply-coupon.php",
            data: { coupon_code: couponCode },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    // Save to local storage for extra safety across redirects
                    localStorage.setItem('backup_coupon_code', couponCode);

                    // Show success message inside the input wrapper
                    let successMsg = `<small id="coupon-message" class="text-success mt-2 d-block">Coupon applied successfully!</small>`;
                    $('#coupon-message').remove();
                    $('.coupon-input').after(successMsg);

                    // Disable button
                    $('#apply-coupon-btn').prop('disabled', true);

                    // Reload page to reflect changes
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    $('#apply-coupon-btn').prop('disabled', false).text('Apply');
                    alert(response.message || "Invalid coupon");
                }
            },
            error: function() {
                $('#apply-coupon-btn').prop('disabled', false).text('Apply');
                alert("Failed to apply coupon. Please try again.");
            }
        });
    }

    // Remove Coupon
    function removeCoupon() {
        $.post("ajax/remove-coupon.php", {}, function (res) {
            if (res.success) {
                localStorage.removeItem('backup_coupon_code');
                location.reload();
            } else {
                alert(res.message || "Failed to remove coupon.");
            }
        }, "json");
    }
    
    // Auto fade out alerts
    setTimeout(function() {
        let alertEl = document.querySelector('.alert');
        if (alertEl) {
            alertEl.classList.remove('show');
            alertEl.classList.add('fade');
            setTimeout(() => alertEl.remove(), 150);
        }
    }, 5000);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function promptLoginOrGuest() {
        Swal.fire({
            title: 'Please Log In',
            text: 'You must be logged in to access checkout, or you can continue as a guest.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#fcb813',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Log In',
            cancelButtonText: 'Continue as Guest',
            reverseButtons: true,
            allowOutsideClick: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'login.php';
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                window.location.href = 'checkout.php?guest=1';
            }
        });
    }
    </script>

    <?php
    include('include/footer.php');
    ?>
    </div>