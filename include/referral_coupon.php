<?php

function referral_cookie_path(): string
{
    if (!defined('BASE_URL')) {
        return '/';
    }
    $path = parse_url(BASE_URL, PHP_URL_PATH);
    if (!$path || $path === '/') {
        return '/';
    }
    return rtrim($path, '/') . '/';
}

function referral_restore_from_cookies(): void
{
    if (!empty($_COOKIE['ref_user_id']) && empty($_SESSION['ref_user_id'])) {
        $_SESSION['ref_user_id'] = (int) $_COOKIE['ref_user_id'];
    }
    if (!isset($_SESSION['product_ref']) || !is_array($_SESSION['product_ref'])) {
        $_SESSION['product_ref'] = [];
    }
    foreach ($_COOKIE as $key => $val) {
        if (strpos($key, 'prod_ref_') === 0) {
            $prod_id = (int) substr($key, 9);
            if ($prod_id > 0 && empty($_SESSION['product_ref'][$prod_id])) {
                $_SESSION['product_ref'][$prod_id] = (int) $val;
            }
        }
    }
}

function referral_resolve_partner_id($ref_val): int
{
    global $pdo;
    if (!$pdo || $ref_val === '' || $ref_val === null) {
        return 0;
    }
    if (is_numeric($ref_val)) {
        $stmt = $pdo->prepare("SELECT id FROM tbl_user WHERE id = ? AND status = 'Active' LIMIT 1");
        $stmt->execute([(int) $ref_val]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM tbl_user WHERE ref_code = ? AND status = 'Active' LIMIT 1");
        $stmt->execute([$ref_val]);
    }
    return $stmt->rowCount() > 0 ? (int) $stmt->fetchColumn() : 0;
}

function referral_save_partner(int $partner_id, ?int $product_id = null): void
{
    if ($partner_id <= 0) {
        return;
    }
    $path  = referral_cookie_path();
    $expiry = time() + (86400 * 30);
    $_SESSION['ref_user_id'] = $partner_id;
    setcookie('ref_user_id', (string) $partner_id, $expiry, $path);
    if ($product_id !== null && $product_id > 0) {
        if (!isset($_SESSION['product_ref']) || !is_array($_SESSION['product_ref'])) {
            $_SESSION['product_ref'] = [];
        }
        $_SESSION['product_ref'][$product_id] = $partner_id;
        setcookie('prod_ref_' . $product_id, (string) $partner_id, $expiry, $path);
    }
}

function referral_capture_from_request(?int $product_id = null): void
{
    referral_restore_from_cookies();
    if (!isset($_GET['ref'])) {
        return;
    }
    unset($_SESSION['coupon_removed']);
    $partner_id = referral_resolve_partner_id($_GET['ref']);
    if ($partner_id > 0) {
        referral_save_partner($partner_id, $product_id);
    }
}

function referral_has_active(): bool
{
    return !empty($_SESSION['ref_user_id']) || !empty($_SESSION['product_ref']);
}

function referral_partner_for_product(int $product_id): int
{
    if (!empty($_SESSION['product_ref'][$product_id])) {
        return (int) $_SESSION['product_ref'][$product_id];
    }
    if (!empty($_SESSION['ref_user_id'])) {
        return (int) $_SESSION['ref_user_id'];
    }
    return 0;
}

function referral_persist_coupon_session(array $coupon): void
{
    $_SESSION['coupon'] = $coupon;
    $path = referral_cookie_path();
    setcookie('backup_coupon_data', json_encode($coupon), time() + (86400 * 30), $path);
}

function referral_restore_coupon_backup(): void
{
    if (isset($_SESSION['coupon']) && is_array($_SESSION['coupon']) && !empty($_SESSION['coupon']['code'])) {
        return;
    }
    if (empty($_COOKIE['backup_coupon_data'])) {
        return;
    }
    $decoded = json_decode($_COOKIE['backup_coupon_data'], true);
    if (is_array($decoded) && !empty($decoded['code'])) {
        $_SESSION['coupon'] = $decoded;
    }
}

function referral_apply_coupon($user_id): bool
{
    global $pdo, $con;
    if (!$user_id || !referral_has_active() || isset($_SESSION['coupon_removed'])) {
        return false;
    }
    if (isset($_SESSION['coupon']) && is_array($_SESSION['coupon']) && !empty($_SESSION['coupon']['code'])) {
        return true;
    }
    if (!$con || !$pdo) {
        return false;
    }

    $user_id_esc = mysqli_real_escape_string($con, (string) $user_id);
    $q = mysqli_query($con, "SELECT p_id, p_actual_price, no_of_item FROM tbl_cart WHERE user_id = '$user_id_esc' AND is_ordered = '0'");
    if (!$q || mysqli_num_rows($q) === 0) {
        return false;
    }

    $cart_items = [];
    while ($row = mysqli_fetch_assoc($q)) {
        $cart_items[] = $row;
    }

    foreach ($cart_items as $item) {
        $item_p_id  = (int) $item['p_id'];
        $partner_id = referral_partner_for_product($item_p_id);
        if ($partner_id <= 0) {
            continue;
        }

        $stmt = $pdo->prepare("
            SELECT uc.*, c.coupon_code, c.amount AS coupon_amount, c.type AS coupon_type, c.p_id AS coupon_p_id
            FROM tbl_user_coupon uc
            JOIN tbl_coupon c ON uc.coupon_id = c.id
            WHERE uc.user_id = ? AND (uc.p_id = ? OR uc.p_id IS NULL OR uc.p_id = 0)
            LIMIT 1
        ");
        $stmt->execute([$partner_id, $item_p_id]);
        $uc = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$uc) {
            continue;
        }

        $coupon_p_id   = (int) $uc['coupon_p_id'];
        $coupon_amount = (float) $uc['coupon_amount'];
        $coupon_type   = $uc['coupon_type'];
        $eligible      = 0.0;

        foreach ($cart_items as $c_item) {
            if ($coupon_p_id === 0 || (int) $c_item['p_id'] === $coupon_p_id) {
                $eligible += (float) $c_item['p_actual_price'] * (int) $c_item['no_of_item'];
            }
        }

        if ($eligible <= 0) {
            continue;
        }

        $discount = $coupon_type === 'percent'
            ? min($eligible, $eligible * $coupon_amount / 100)
            : min($eligible, $coupon_amount);

        referral_persist_coupon_session([
            'code'   => $uc['coupon_code'],
            'amount' => $discount,
            'p_id'   => $coupon_p_id,
            'type'   => $coupon_type,
        ]);
        return true;
    }

    return false;
}
