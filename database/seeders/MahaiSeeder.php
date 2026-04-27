<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MahaiSeeder extends Seeder
{
    public function run(): void
    {
        // Si la tabla no existe, evitamos fallo.
        try {
            DB::table('mahaiak')->delete();
        } catch (\Throwable $e) {
            return;
        }

        for ($i = 1; $i <= 5; $i++) {
            DB::table('mahaiak')->insert([
                'izena' => "Mahaia {$i}",
                'egoera' => 'Libre',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
