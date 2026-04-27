<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduktuenMota extends Model
{
    protected $table = 'produktuen_motak';

    public $timestamps = false;

    protected $fillable = [
        'izena',
    ];

    public function produktuak()
    {
        return $this->hasMany(Produktua::class, 'produktuen_motak_id');
    }
}
