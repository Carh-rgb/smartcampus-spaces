<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Reservation;
use App\Models\Resource;
use App\Models\Space;

/**
 * Panel de inicio: resumen del estado del campus y accesos a cada modulo.
 * Es un controlador invocable (un solo metodo __invoke).
 */
class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();

        $metrics = [
            'spaces' => Space::where('status', 'available')->count(),
            'resources' => Resource::count(),
            'pending' => Reservation::where('status', 'pending')->count(),
            'incidents' => Incident::whereIn('status', ['open', 'in_progress'])->count(),
        ];

        // El administrador ve la cola de aprobacion; el solicitante, lo suyo.
        $reservations = Reservation::query()
            ->with(['user', 'space'])
            ->unless($user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->when($user->isAdmin(), fn ($query) => $query->where('status', 'pending'))
            ->orderBy('date')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        $incidents = Incident::query()
            ->with(['reservation.space'])
            ->unless($user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->whereIn('status', ['open', 'in_progress'])
            ->orderByDesc('reported_at')
            ->take(5)
            ->get();

        return view('dashboard', compact('metrics', 'reservations', 'incidents'));
    }
}
