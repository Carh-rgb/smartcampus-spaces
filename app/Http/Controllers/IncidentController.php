<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Novedades reportadas sobre una reserva ya realizada. Ademas del CRUD
 * expone la asignacion y el cierre del incidente (verbo PATCH).
 */
class IncidentController extends Controller
{
    /**
     * GET /incidents - El administrador ve todos; el solicitante, los suyos.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $incidents = Incident::query()
            ->with(['reservation.space', 'user', 'assignee'])
            ->unless($user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
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
            'reservations' => $this->reportableReservations(),
            'selected' => $request->integer('reservation_id'),
        ]);
    }

    /**
     * POST /incidents - Registra la novedad sobre la reserva elegida.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $this->authorizeReservation($validated['reservation_id']);

        $incident = Incident::create($validated + [
            'user_id' => auth()->id(),
            'status' => 'open',
            'reported_at' => now(),
        ]);

        return redirect()
            ->route('incidents.show', $incident)
            ->with('success', 'Incidente reportado. Queda abierto para su revision.');
    }

    /**
     * GET /incidents/{incident} - Detalle del incidente.
     */
    public function show(Incident $incident)
    {
        $this->authorizeOwnerOrAdmin($incident);

        $incident->load(['reservation.space', 'user', 'assignee']);

        return view('incidents.show', [
            'incident' => $incident,
            'admins' => User::where('role', 'admin')->orderBy('name')->get(),
        ]);
    }

    /**
     * GET /incidents/{incident}/edit - Formulario de edicion del reporte.
     */
    public function edit(Incident $incident)
    {
        $this->authorizeOwnerOrAdmin($incident);

        return view('incidents.edit', [
            'incident' => $incident,
            'reservations' => $this->reportableReservations(),
        ]);
    }

    /**
     * PUT/PATCH /incidents/{incident} - Actualiza el reporte.
     */
    public function update(Request $request, Incident $incident)
    {
        $this->authorizeOwnerOrAdmin($incident);

        $validated = $request->validate($this->rules());

        $this->authorizeReservation($validated['reservation_id']);

        $incident->update($validated);

        return redirect()
            ->route('incidents.show', $incident)
            ->with('success', 'Reporte actualizado.');
    }

    /**
     * DELETE /incidents/{incident} - Borrado logico del reporte.
     */
    public function destroy(Incident $incident)
    {
        $this->authorizeOwnerOrAdmin($incident);

        $incident->delete();

        return redirect()
            ->route('incidents.index')
            ->with('success', 'Reporte eliminado.');
    }

    /**
     * PATCH /incidents/{incident}/assign - El administrador toma el caso.
     */
    public function assign(Request $request, Incident $incident)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $incident->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => 'in_progress',
        ]);

        return back()->with('success', 'Incidente asignado. Queda en proceso.');
    }

    /**
     * PATCH /incidents/{incident}/resolve - Cierre con la solucion aplicada.
     */
    public function resolve(Request $request, Incident $incident)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'solution' => ['required', 'string', 'max:500'],
        ]);

        $incident->update([
            'solution' => $validated['solution'],
            'status' => 'resolved',
            'resolved_at' => now(),
            'assigned_to' => $incident->assigned_to ?? auth()->id(),
        ]);

        return back()->with('success', 'Incidente resuelto.');
    }

    private function rules(): array
    {
        return [
            'reservation_id' => ['required', 'exists:reservations,id'],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:1000'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
        ];
    }

    /**
     * Reservas sobre las que el usuario puede reportar una novedad.
     */
    private function reportableReservations()
    {
        $user = auth()->user();

        return Reservation::query()
            ->with('space')
            ->unless($user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->whereIn('status', ['approved', 'cancelled'])
            ->orderByDesc('date')
            ->get();
    }

    /**
     * Nadie puede reportar sobre una reserva ajena, salvo el administrador.
     */
    private function authorizeReservation(int $reservationId): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        $propia = Reservation::where('id', $reservationId)
            ->where('user_id', $user->id)
            ->exists();

        abort_unless($propia, 403, 'Solo se puede reportar sobre una reserva propia.');
    }

    private function authorizeOwnerOrAdmin(Incident $incident): void
    {
        $user = auth()->user();

        abort_unless($user->isAdmin() || $incident->user_id === $user->id, 403);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Solo el administrador gestiona la solucion de los incidentes.');
    }
}
