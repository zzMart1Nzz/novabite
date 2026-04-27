<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produktua extends Model
{
    protected $table = 'produktuak';

    public $timestamps = false;

    protected $fillable = [
        'izena',
        'prezioa',
        'mota',
        'stock',
        'irudia',
        'irudia_path',
        'produktuen_motak_id',
    ];

    public function mota()
    {
        return $this->belongsTo(ProduktuenMota::class, 'produktuen_motak_id');
    }
}
