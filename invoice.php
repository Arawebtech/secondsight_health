<?php
ob_start();
include("admin/inc/config.php");
include_once('TCPDF-main/tcpdf.php');

function fetchOrderAndShip($con, $order_id) {
    // Fetch order
    $query = "SELECT * FROM tbl_order WHERE order_id = '$order_id' LIMIT 1";
    $result = mysqli_query($con, $query) or die("Order Query Failed: " . mysqli_error($con));
    if (mysqli_num_rows($result) == 0) return null;
    $order = mysqli_fetch_assoc($result);

    $user_id = $order['user_id'];

    // Fetch shipping address
    $query_ship = "SELECT name, phone_no, building_no, street_address, landmark, town, district, state, pincode 
                   FROM tbl_shipping_address 
                   WHERE user_id = '$user_id' LIMIT 1";
    $res_ship = mysqli_query($con, $query_ship) or die("Shipping Query Failed: " . mysqli_error($con));
    if (mysqli_num_rows($res_ship) == 0) return null;
    $ship = mysqli_fetch_assoc($res_ship);

    $parts = array_filter([
        $ship['building_no'],
        $ship['street_address'],
        $ship['landmark'],
        $ship['town'],
        $ship['district'],
        $ship['state'] . ' - ' . $ship['pincode']
    ]);
    $fullAddress = implode(', ', $parts);

    return [$ship, $fullAddress, $order];
}

// Initialize TCPDF
$pdf = new TCPDF('P', 'mm', 'A5', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Second Sight Foundation');
$pdf->SetTitle('Shipping Labels');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetFont('helvetica', '', 10);

// ---- Single Order ----
if (isset($_POST['download_label']) && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $data = fetchOrderAndShip($con, $order_id);
    if (!$data) die("❌ Order/Shipping not found for ID $order_id");

    list($ship, $fullAddress, $order) = $data;

    $pdf->AddPage();
    
    $html = '
    <table cellpadding="5" style="font-family: helvetica; font-size: 14pt; font-weight: bold; width: 100%;">
        <tr>
            <td height="85" valign="top" style="border-bottom: 1px dashed #666;">
                <span style="font-size: 16pt;">To</span><br>
                Name: ' . htmlspecialchars($ship['name']) . '<br>
                Address: ' . htmlspecialchars($fullAddress) . '<br>
                Mobile: ' . htmlspecialchars($ship['phone_no']) . '
            </td>
        </tr>
        <tr>
            <td height="85" valign="bottom">
                <span style="font-size: 16pt;">To</span><br>
                Name: ' . htmlspecialchars($ship['name']) . '<br>
                Address: ' . htmlspecialchars($fullAddress) . '<br>
                Mobile: ' . htmlspecialchars($ship['phone_no']) . '
            </td>
        </tr>
    </table>';
    
    $pdf->writeHTML($html, true, false, true, false, '');

    $fileName = "Shipping_Label_{$order['order_id']}.pdf";
}

// ---- Multiple Orders ----
elseif (isset($_POST['download_labels_bulk']) && !empty($_POST['order_ids'])) {
    $orderIds = $_POST['order_ids'];

    foreach ($orderIds as $order_id) {
        $data = fetchOrderAndShip($con, $order_id);
        if (!$data) continue;

        list($ship, $fullAddress, $order) = $data;

        $pdf->AddPage();
        
        $html = '
        <table cellpadding="5" style="font-family: helvetica; font-size: 14pt; font-weight: bold; width: 100%;">
            <tr>
                <td height="85" valign="top" style="border-bottom: 1px dashed #666;">
                    <span style="font-size: 16pt;">To</span><br>
                    Name: ' . htmlspecialchars($ship['name']) . '<br>
                    Address: ' . htmlspecialchars($fullAddress) . '<br>
                    Mobile: ' . htmlspecialchars($ship['phone_no']) . '
                </td>
            </tr>
            <tr>
                <td height="85" valign="bottom">
                    <span style="font-size: 16pt;">To</span><br>
                    Name: ' . htmlspecialchars($ship['name']) . '<br>
                    Address: ' . htmlspecialchars($fullAddress) . '<br>
                    Mobile: ' . htmlspecialchars($ship['phone_no']) . '
                </td>
            </tr>
        </table>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
    }

    $fileName = "Shipping_Labels_Bulk.pdf";
} else {
    die("❌ No order selected.");
}

// ---- Output ----
ini_set('display_errors', 0);

if (ob_get_length()) {
    ob_end_clean();
}

// Output PDF as download
$pdf->Output($fileName, 'D');
exit();
