<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlateraMota extends Model
{
    use HasFactory;

    protected $table = 'platera_motak';

    public $timestamps = false;

    protected $fillable = [
        'izena',
    ];

    public function platerak()
    {
        return $this->hasMany(Platera::class, 'platera_motak_id');
    }
}
