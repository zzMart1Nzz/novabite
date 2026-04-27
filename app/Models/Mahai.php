<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahai extends Model
{
    use HasFactory;

    protected $table = 'mahaiak';

    public $timestamps = false;

    protected $fillable = [
        'zenbakia',
        'pertsona_kopurua',
        'kokapena',
    ];

    public function erreserbak()
    {
        return $this->hasMany(Erreserba::class, 'mahaiak_id');
    }
}
