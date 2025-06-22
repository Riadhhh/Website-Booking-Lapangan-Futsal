<?php
require_once __DIR__ . '/configs.php';

function createInvoiceAndSave(array $data) {
    $kodeInvoice = 'INV' . time() . rand(100, 999);

    $insert = sql("INSERT INTO pemesanan 
        (nama, nomorhp, waktubermain, tglpesan, jam_mulai, durasi, airmineral, diskon, final, kode_invoice, status_pembayaran)
        VALUES
        (:nama, :nomorhp, :waktubermain, :tglpesan, :jam_mulai, :durasi, :airmineral, :diskon, :final, :kode_invoice, 'pending')",
        [
            ':nama'         => $data['nama'],
            ':nomorhp'      => $data['nomorhp'],
            ':waktubermain' => $data['waktubermain'],
            ':tglpesan'     => $data['tglpesan'],
            ':jam_mulai'    => $data['jam_mulai'],
            ':durasi'       => $data['durasi'],
            ':airmineral'   => $data['airmineral'],
            ':diskon'       => $data['diskon'],
            ':final'        => $data['final'],
            ':kode_invoice' => $kodeInvoice
        ]);

    if ($insert['row'] > 0) {
        return [
            'success' => true,
            'kode_invoice' => $kodeInvoice
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Gagal menyimpan data pemesanan'
        ];
    }
}