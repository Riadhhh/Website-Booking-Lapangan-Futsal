<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    protected $table = 'pemesanan';
    public $timestamps = false;

    protected $fillable = [
        'nama',
        'nomorhp',
        'waktubermain',
        'tglpesan',
        'jam_mulai',
        'durasi',
        'airmineral',
        'diskon',
        'final',
        'status_pembayaran',
        'kode_invoice',
    ];

    protected $casts = [
        'tglpesan' => 'date',
        'jam_mulai' => 'datetime:H:i',
    ];
}
