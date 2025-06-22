<?php
require_once __DIR__ . "/config/configs.php";

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$kode_invoice = $data['kode_invoice'] ?? null;
$status      = $data['status'] ?? null;

if (!$kode_invoice || !$status) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing kode_invoice or status']);
    exit;
}

if ($status === 'PAID' || $status === 'SUCCESS') {
    sql("UPDATE pemesanan SET status_pembayaran = 'paid' WHERE kode_invoice = :kode", [
        ':kode' => $kode_invoice
    ]);
} elseif ($status === 'EXPIRED' || $status === 'CANCELLED') {
    sql("UPDATE pemesanan SET status_pembayaran = 'expired' WHERE kode_invoice = :kode", [
        ':kode' => $kode_invoice
    ]);
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Callback received']);
