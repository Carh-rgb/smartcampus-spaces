<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Reservation;
use App\Models\Resource;
use App\Models\Space;
use Illuminate\Http\Request;

/**
 * Panel de inicio: resumen del estado del campus y accesos a cada modulo.
 * Es un controlador invocable (un solo metodo __invoke).
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $metrics = [
            'spaces' => Space::where('status', 'available')->count(),
            'resources' => Resource::count(),
            'pending' => Reservation::where('status', 'pending')->count(),
            'incidents' => Incident::unresolved()->count(),
        ];

        // El administrador ve la cola de aprobacion; el solicitante, lo suyo.
        $reservations = Reservation::query()
            ->with(['user', 'space'])
            ->visibleTo($user)
            ->when($user->isAdmin(), fn ($query) => $query->where('status', 'pending'))
            ->orderBy('date')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        $incidents = Incident::query()
            ->with('reservation.space')
            ->visibleTo($user)
            ->unresolved()
            ->latest('reported_at')
            ->take(5)
            ->get();

        return view('dashboard', compact('metrics', 'reservations', 'incidents'));
    }
}
