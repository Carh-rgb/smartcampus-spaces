<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\SpaceResourceController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Rutas publicas
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Rutas de la aplicacion (requieren sesion iniciada)
|--------------------------------------------------------------------------
| Cada entidad del dominio se expone con Route::resource, que genera las
| siete rutas nombradas del CRUD (index, create, store, show, edit, update
| y destroy) con sus verbos HTTP correspondientes. Las acciones que cambian
| el estado de un registro se declaran aparte con el verbo PATCH.
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Espacios del campus: aulas, laboratorios, auditorios y salas.
    Route::resource('spaces', SpaceController::class);

    // Recursos asignados a un espacio (tabla pivote resource_space).
    Route::post('spaces/{space}/resources', [SpaceResourceController::class, 'store'])
        ->name('spaces.resources.store');
    Route::delete('spaces/{space}/resources/{resource}', [SpaceResourceController::class, 'destroy'])
        ->name('spaces.resources.destroy');

    // Catalogo de recursos tecnologicos.
    Route::resource('resources', ResourceController::class);

    // Solicitudes de reserva y sus cambios de estado.
    Route::resource('reservations', ReservationController::class);
    Route::patch('reservations/{reservation}/approve', [ReservationController::class, 'approve'])
        ->name('reservations.approve');
    Route::patch('reservations/{reservation}/reject', [ReservationController::class, 'reject'])
        ->name('reservations.reject');
    Route::patch('reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])
        ->name('reservations.cancel');

    // Incidentes reportados sobre una reserva.
    Route::resource('incidents', IncidentController::class);
    Route::patch('incidents/{incident}/assign', [IncidentController::class, 'assign'])
        ->name('incidents.assign');
    Route::patch('incidents/{incident}/resolve', [IncidentController::class, 'resolve'])
        ->name('incidents.resolve');
});

/*
|--------------------------------------------------------------------------
| Perfil del usuario (starter kit)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
