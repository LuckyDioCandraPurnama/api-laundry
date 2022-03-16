<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetilTransaksi extends Model
{
    protected $table = 'detil_transaksi';
    protected $fillable = ['id_transaksi', 'id_paket', 'quantity', 'subtotal'];
    // protected $timestamp = false;
}
