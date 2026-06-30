<?php
session_start();
$_SESSION['ref_user_id'] = 256;
echo "Before login: " . print_r($_SESSION, true) . "\n";

// simulate login
$_SESSION['user_id'] = 100;
$_SESSION['email_id'] = 'test@test.com';

echo "After login: " . print_r($_SESSION, true) . "\n";
?>
