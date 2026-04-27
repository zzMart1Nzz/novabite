<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Erreserba extends Model
{
    use HasFactory;

    protected $table = 'erreserbak';

    public $timestamps = false;

    protected $fillable = [
        'bezero_izena',
        'telefonoa',
        'pertsona_kopurua',
        'eguna_ordua',
        'prezio_totala',
        'ordainduta',
        'faktura_ruta',
        'langileak_id',
        'mahaiak_id',
    ];

    protected $casts = [
        'eguna_ordua' => 'datetime',
        'ordainduta' => 'boolean',
    ];

    public function mahai()
    {
        return $this->belongsTo(Mahai::class, 'mahaiak_id');
    }
}
