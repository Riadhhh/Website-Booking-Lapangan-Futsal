<?php
require_once __DIR__ . '/tripay_config.php';

function createTripayInvoice($amount, $customerName, $customerPhone, $merchantRef) {
    $apiKey = TRIPAY_API_KEY;
    $merchantCode = TRIPAY_MERCHANT_CODE;
    $privateKey = TRIPAY_PRIVATE_KEY;
    $apiUrl = TRIPAY_API_URL . 'transaction/create';

    $signature = hash_hmac('sha256', $merchantCode . $merchantRef . $amount, $privateKey);

    $data = [
        'method'         => 'BRIVA', // contoh metode (BCA, QRIS, OVO, DLL - bisa kamu ubah nanti)
        'merchant_ref'   => $merchantRef,
        'amount'         => $amount,
        'customer_name'  => $customerName,
        'customer_email' => 'test@example.com',
        'customer_phone' => $customerPhone,
        'order_items'    => [
            [
                'sku'   => 'booking-lapangan',
                'name'  => 'Booking Lapangan Futsal',
                'price' => $amount,
                'quantity' => 1
            ]
        ],
        'callback_url'   => 'https://example.com/callback.php', 
        'return_url'     => 'http://localhost/Akhir/res/strukpemesanan.php?ref=' . $merchantRef,
        'signature'      => $signature
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_FRESH_CONNECT  => true,
        CURLOPT_URL            => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
        CURLOPT_FAILONERROR    => false,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($data),
        CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) return ['success' => false, 'message' => $error];
    
    $result = json_decode($response, true);
    return $result;
}
