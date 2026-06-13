<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['coupon'])) {
    unset($_SESSION['coupon']);
    $_SESSION['coupon_removed'] = true;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
