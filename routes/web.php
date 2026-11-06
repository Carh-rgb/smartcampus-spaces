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
| Vistas estaticas (publicas)
|--------------------------------------------------------------------------
| No necesitan datos de la base de datos, por eso se declaran con
| Route::view en lugar de un controlador o una funcion anonima.
*/

Route::view('/', 'welcome')->name('home');
Route::view('acerca', 'about')->name('about');

/*
|--------------------------------------------------------------------------
| Vistas dinamicas (requieren sesion iniciada)
|--------------------------------------------------------------------------
| Cada entidad del dominio tiene su propio bloque:
|  - Route::resource genera las siete rutas nombradas del CRUD (index,
|    create, store, show, edit, update y destroy) con sus verbos HTTP.
|  - Las acciones extra se agrupan con controller() + prefix() + name(),
|    asi comparten el controlador, la URL base y el prefijo del nombre.
|  - Las acciones que solo cambian el estado de un registro usan PATCH.
| Los permisos de cada accion se resuelven en app/Policies.
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Panel de inicio (controlador invocable).
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Espacios del campus: aulas, laboratorios, auditorios y salas.
    Route::resource('spaces', SpaceController::class);

    // Recursos asignados a un espacio (tabla pivote resource_space).
    Route::controller(SpaceResourceController::class)
        ->prefix('spaces/{space}/resources')
        ->name('spaces.resources.')
        ->group(function () {
            Route::post('/', 'store')->name('store');
            Route::delete('{resource}', 'destroy')->name('destroy');
        });

    // Catalogo de recursos tecnologicos.
    Route::resource('resources', ResourceController::class);

    // Solicitudes de reserva y sus cambios de estado.
    Route::resource('reservations', ReservationController::class);

    Route::controller(ReservationController::class)
        ->prefix('reservations/{reservation}')
        ->name('reservations.')
        ->group(function () {
            Route::patch('approve', 'approve')->name('approve');
            Route::patch('reject', 'reject')->name('reject');
            Route::patch('cancel', 'cancel')->name('cancel');
        });

    // Incidentes reportados sobre una reserva.
    Route::resource('incidents', IncidentController::class);

    Route::controller(IncidentController::class)
        ->prefix('incidents/{incident}')
        ->name('incidents.')
        ->group(function () {
            Route::patch('assign', 'assign')->name('assign');
            Route::patch('resolve', 'resolve')->name('resolve');
        });
});

/*
|--------------------------------------------------------------------------
| Configuracion de la cuenta (componentes Livewire Volt del starter kit)
|--------------------------------------------------------------------------
*/

Route::redirect('settings', '/settings/profile')->middleware('auth');

Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
    Volt::route('profile', 'settings.profile')->name('profile');
    Volt::route('password', 'settings.password')->name('password');
    Volt::route('appearance', 'settings.appearance')->name('appearance');
});

require __DIR__.'/auth.php';
