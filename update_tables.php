<?php

use App\Models\Mahai;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mahaiak = Mahai::all();
$count = 0;

foreach ($mahaiak as $mahai) {
    // Force update to ensure format "Mahaia ID"
    $newName = 'Mahaia '.$mahai->id;
    if ($mahai->izena !== $newName) {
        $mahai->izena = $newName;
        $mahai->save();
        echo "Updated table {$mahai->id} to {$mahai->izena}\n";
        $count++;
    }
}

echo "Total updated: $count\n";
