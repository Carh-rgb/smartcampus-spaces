<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Solicitudes de uso de un espacio. Ademas de las siete rutas de
 * Route::resource, expone acciones de estado (aprobar, rechazar,
 * cancelar) mapeadas al verbo PATCH.
 */
class ReservationController extends Controller
{
    /**
     * GET /reservations - El administrador ve todas; el solicitante, las suyas.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $reservations = Reservation::query()
            ->with(['user', 'space', 'approver'])
            ->unless($user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->orderByDesc('date')
            ->orderBy('start_time')
            ->get();

        return view('reservations.index', compact('reservations'));
    }

    /**
     * GET /reservations/create - Formulario de solicitud.
     */
    public function create()
    {
        return view('reservations.create', [
            'reservation' => new Reservation,
            'spaces' => $this->availableSpaces(),
        ]);
    }

    /**
     * POST /reservations - Registra la solicitud en estado pendiente.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $this->ensureNoOverlap($validated);

        $reservation = Reservation::create($validated + [
            'user_id' => auth()->id(),
            'status' => 'pending',
        ]);

        return redirect()
            ->route('reservations.show', $reservation)
            ->with('success', 'Solicitud enviada. Queda pendiente de aprobacion.');
    }

    /**
     * GET /reservations/{reservation} - Detalle con sus incidentes.
     */
    public function show(Reservation $reservation)
    {
        $this->authorizeOwnerOrAdmin($reservation);

        $reservation->load(['user', 'space', 'approver', 'incidents.user']);

        return view('reservations.show', compact('reservation'));
    }

    /**
     * GET /reservations/{reservation}/edit - Solo mientras siga pendiente.
     */
    public function edit(Reservation $reservation)
    {
        $this->authorizeOwnerOrAdmin($reservation);

        abort_unless($reservation->status === 'pending', 403, 'Solo se puede editar una solicitud pendiente.');

        return view('reservations.edit', [
            'reservation' => $reservation,
            'spaces' => $this->availableSpaces(),
        ]);
    }

    /**
     * PUT/PATCH /reservations/{reservation} - Actualiza la solicitud.
     */
    public function update(Request $request, Reservation $reservation)
    {
        $this->authorizeOwnerOrAdmin($reservation);

        $validated = $request->validate($this->rules());

        $this->ensureNoOverlap($validated, $reservation);

        $reservation->update($validated);

        return redirect()
            ->route('reservations.show', $reservation)
            ->with('success', 'Solicitud actualizada.');
    }

    /**
     * DELETE /reservations/{reservation} - Borrado logico de la solicitud.
     */
    public function destroy(Reservation $reservation)
    {
        $this->authorizeOwnerOrAdmin($reservation);

        $reservation->delete();

        return redirect()
            ->route('reservations.index')
            ->with('success', 'Solicitud eliminada.');
    }

    /**
     * PATCH /reservations/{reservation}/approve - Decision del administrador.
     */
    public function approve(Request $request, Reservation $reservation)
    {
        $this->authorizeAdmin();

        $reservation->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $request->input('review_notes'),
        ]);

        return back()->with('success', 'Reserva aprobada.');
    }

    /**
     * PATCH /reservations/{reservation}/reject - Decision del administrador.
     */
    public function reject(Request $request, Reservation $reservation)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'max:500'],
        ]);

        $reservation->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'],
        ]);

        return back()->with('success', 'Reserva rechazada.');
    }

    /**
     * PATCH /reservations/{reservation}/cancel - El solicitante libera el espacio.
     */
    public function cancel(Reservation $reservation)
    {
        $this->authorizeOwnerOrAdmin($reservation);

        abort_if($reservation->status === 'rejected', 403, 'Una reserva rechazada no se puede cancelar.');

        $reservation->update(['status' => 'cancelled']);

        return back()->with('success', 'Reserva cancelada. El espacio queda libre.');
    }

    /**
     * Reglas de validacion de la solicitud.
     */
    private function rules(): array
    {
        return [
            'space_id' => ['required', 'exists:spaces,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'purpose' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Valida el cruce de horarios: la base de datos tiene el indice
     * (space_id, date, start_time), pero la regla de negocio vive aqui
     * porque debe ignorar las reservas rechazadas y canceladas.
     */
    private function ensureNoOverlap(array $data, ?Reservation $ignore = null): void
    {
        $existe = Reservation::query()
            ->where('space_id', $data['space_id'])
            ->whereDate('date', $data['date'])
            ->whereIn('status', ['pending', 'approved'])
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->exists();

        if ($existe) {
            throw ValidationException::withMessages([
                'start_time' => 'El espacio ya tiene una reserva activa que se cruza con ese horario.',
            ]);
        }
    }

    /**
     * Espacios que se pueden reservar (los que estan en mantenimiento no).
     */
    private function availableSpaces()
    {
        return Space::query()
            ->where('status', 'available')
            ->orderBy('building')
            ->orderBy('name')
            ->get();
    }

    private function authorizeOwnerOrAdmin(Reservation $reservation): void
    {
        $user = auth()->user();

        abort_unless($user->isAdmin() || $reservation->user_id === $user->id, 403);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Solo el administrador aprueba o rechaza reservas.');
    }
}
