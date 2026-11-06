<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignIncidentRequest;
use App\Http\Requests\IncidentRequest;
use App\Http\Requests\ResolveIncidentRequest;
use App\Models\Incident;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Novedades reportadas sobre una reserva ya realizada. Ademas del CRUD
 * expone la asignacion y el cierre del incidente (verbo PATCH).
 * Permisos: IncidentPolicy. Validacion: IncidentRequest y afines.
 */
class IncidentController extends Controller
{
    /**
     * GET /incidents - El administrador ve todos; el solicitante, los suyos.
     */
    public function index(Request $request)
    {
        $incidents = Incident::query()
            ->with(['reservation.space', 'user', 'assignee'])
            ->visibleTo($request->user())
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->byPriority()
            ->orderByDesc('reported_at')
            ->get();

        return view('incidents.index', compact('incidents'));
    }

    /**
     * GET /incidents/create - Formulario de reporte.
     */
    public function create(Request $request)
    {
        return view('incidents.create', [
            'incident' => new Incident,
            'reservations' => $this->reportableReservations($request->user()),
            'selected' => $request->integer('reservation_id'),
        ]);
    }

    /**
     * POST /incidents - Registra la novedad sobre la reserva elegida.
     */
    public function store(IncidentRequest $request)
    {
        $incident = $request->user()->incidents()->create(
            $request->validated() + ['status' => 'open', 'reported_at' => now()]
        );

        return to_route('incidents.show', $incident)
            ->with('success', 'Incidente reportado. Queda abierto para su revisión.');
    }

    /**
     * GET /incidents/{incident} - Detalle del incidente.
     */
    public function show(Incident $incident)
    {
        $this->authorize('view', $incident);

        $incident->load(['reservation.space', 'user', 'assignee']);

        return view('incidents.show', [
            'incident' => $incident,
            'admins' => User::where('role', 'admin')->orderBy('name')->get(),
        ]);
    }

    /**
     * GET /incidents/{incident}/edit - Formulario de edicion del reporte.
     */
    public function edit(Request $request, Incident $incident)
    {
        $this->authorize('update', $incident);

        return view('incidents.edit', [
            'incident' => $incident,
            'reservations' => $this->reportableReservations($request->user()),
        ]);
    }

    /**
     * PUT/PATCH /incidents/{incident} - Actualiza el reporte.
     */
    public function update(IncidentRequest $request, Incident $incident)
    {
        $this->authorize('update', $incident);

        $incident->update($request->validated());

        return to_route('incidents.show', $incident)->with('success', 'Reporte actualizado.');
    }

    /**
     * DELETE /incidents/{incident} - Borrado logico del reporte.
     */
    public function destroy(Incident $incident)
    {
        $this->authorize('delete', $incident);

        $incident->delete();

        return to_route('incidents.index')->with('success', 'Reporte eliminado.');
    }

    /**
     * PATCH /incidents/{incident}/assign - El administrador toma el caso.
     */
    public function assign(AssignIncidentRequest $request, Incident $incident)
    {
        $this->authorize('manage', $incident);

        $incident->assignTo($request->integer('assigned_to'));

        return back()->with('success', 'Incidente asignado. Queda en proceso.');
    }

    /**
     * PATCH /incidents/{incident}/resolve - Cierre con la solucion aplicada.
     */
    public function resolve(ResolveIncidentRequest $request, Incident $incident)
    {
        $this->authorize('manage', $incident);

        $incident->resolve($request->validated('solution'), $request->user());

        return back()->with('success', 'Incidente resuelto.');
    }

    /**
     * Reservas del usuario sobre las que se puede reportar una novedad.
     */
    private function reportableReservations(User $user)
    {
        return Reservation::query()->with('space')->reportableBy($user)->orderByDesc('date')->get();
    }
}
