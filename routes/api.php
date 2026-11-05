<?php

use App\Http\Controllers\Api\SpaceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de API
|--------------------------------------------------------------------------
| Consulta de espacios y de su disponibilidad en formato JSON, pensada
| para un frontend desacoplado o una app movil. Laravel antepone el
| prefijo /api a todas las rutas de este archivo.
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('spaces', [SpaceController::class, 'index'])->name('spaces.index');
    Route::get('spaces/{space}', [SpaceController::class, 'show'])->name('spaces.show');
    Route::get('spaces/{space}/availability', [SpaceController::class, 'availability'])->name('spaces.availability');
});
