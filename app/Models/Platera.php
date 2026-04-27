<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Platera extends Model
{
    use HasFactory;

    protected $table = 'platerak';

    public $timestamps = false;

    protected $fillable = [
        'izena',
        'mota',
        'perezioa',
        'platera_motak_id',
    ];

    public function motaRel()
    {
        return $this->belongsTo(PlateraMota::class, 'platera_motak_id');
    }
}
