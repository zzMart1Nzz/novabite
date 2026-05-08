<?php

use App\Http\Controllers\PublicPagesController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', [PublicPagesController::class, 'home'])->name('home');
Route::get('/filosofia', [PublicPagesController::class, 'filosofia'])->name('filosofia');
Route::get('/menu', [PublicPagesController::class, 'menu'])->name('menu');
Route::get('/restaurantes', [PublicPagesController::class, 'restaurantes'])->name('restaurantes');
Route::get('/images-external/{filename}', function (string $filename) {
    $safeName = basename($filename);
    if ($safeName !== $filename) {
        abort(404);
    }

    $ext = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));
    if (! in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true)) {
        abort(404);
    }

    $path = base_path('external_api/images/'.$safeName);
    if (! file_exists($path)) {
        abort(404);
    }

    return response()->file($path, [
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->name('external.images.show');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Volt::route('/reservas', 'reservas')->middleware('verified')->name('reservas');
    Volt::route('/mis-reservas', 'reservas-historial')->name('reservas.historial');

    Route::get('settings', function () {
        return redirect()->route('profile.edit');
    });

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Route::get('settings/password', function () {
        return redirect()->to(route('profile.edit').'#password');
    })->name('user-password.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(['password.confirm'])
        ->name('two-factor.show');
});
