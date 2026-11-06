<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectReservationRequest;
use App\Http\Requests\ReservationRequest;
use App\Http\Requests\ReviewReservationRequest;
use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Http\Request;

/**
 * Solicitudes de uso de un espacio. Ademas de las siete rutas de
 * Route::resource, expone acciones de estado (aprobar, rechazar,
 * cancelar) mapeadas al verbo PATCH.
 * Permisos: ReservationPolicy. Validacion: ReservationRequest
 * (incluye el control de cruce de horarios).
 */
class ReservationController extends Controller
{
    /**
     * GET /reservations - El administrador ve todas; el solicitante, las suyas.
     */
    public function index(Request $request)
    {
        $reservations = Reservation::query()
            ->with(['user', 'space', 'approver'])
            ->visibleTo($request->user())
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('date')
            ->orderBy('start_time')
            ->paginate(10)
            ->withQueryString();

        return view('reservations.index', compact('reservations'));
    }

    /**
     * GET /reservations/create - Formulario de solicitud.
     */
    public function create()
    {
        return view('reservations.create', [
            'reservation' => new Reservation,
            'spaces' => Space::bookable()->get(),
        ]);
    }

    /**
     * POST /reservations - Registra la solicitud en estado pendiente.
     */
    public function store(ReservationRequest $request)
    {
        $reservation = $request->user()->reservations()->create(
            $request->validated() + ['status' => 'pending']
        );

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Solicitud enviada. Queda pendiente de aprobación.');
    }

    /**
     * GET /reservations/{reservation} - Detalle con sus incidentes.
     */
    public function show(Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        $reservation->load(['user', 'space', 'approver', 'incidents.user']);

        return view('reservations.show', compact('reservation'));
    }

    /**
     * GET /reservations/{reservation}/edit - Solo mientras siga pendiente.
     */
    public function edit(Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        return view('reservations.edit', [
            'reservation' => $reservation,
            'spaces' => Space::bookable()->get(),
        ]);
    }

    /**
     * PUT/PATCH /reservations/{reservation} - Actualiza la solicitud pendiente.
     */
    public function update(ReservationRequest $request, Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        $reservation->update($request->validated());

        return redirect()->route('reservations.show', $reservation)->with('success', 'Solicitud actualizada.');
    }

    /**
     * DELETE /reservations/{reservation} - Borrado logico de la solicitud.
     */
    public function destroy(Reservation $reservation)
    {
        $this->authorize('delete', $reservation);

        $reservation->delete();

        return redirect()->route('reservations.index')->with('success', 'Solicitud eliminada.');
    }

    /**
     * PATCH /reservations/{reservation}/approve - Decision del administrador.
     */
    public function approve(ReviewReservationRequest $request, Reservation $reservation)
    {
        $this->authorize('review', $reservation);

        $reservation->approve($request->user(), $request->validated('review_notes'));

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Reserva aprobada. El espacio queda apartado para el solicitante.');
    }

    /**
     * PATCH /reservations/{reservation}/reject - Decision del administrador.
     */
    public function reject(RejectReservationRequest $request, Reservation $reservation)
    {
        $this->authorize('review', $reservation);

        $reservation->reject($request->user(), $request->validated('rejection_reason'));

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Reserva rechazada. El solicitante verá el motivo en el detalle.');
    }

    /**
     * PATCH /reservations/{reservation}/cancel - El solicitante libera el espacio.
     */
    public function cancel(Reservation $reservation)
    {
        $this->authorize('cancel', $reservation);

        $reservation->cancel();

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Reserva cancelada. El espacio queda libre para otros solicitantes.');
    }
}
