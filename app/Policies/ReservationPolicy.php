<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Permisos sobre las solicitudes de reserva. El solicitante maneja las
 * suyas; el administrador ve todas y es el unico que decide.
 */
class ReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Reservation $reservation): bool
    {
        return $this->ownerOrAdmin($user, $reservation);
    }

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Solo se edita mientras sigue pendiente (aplica a edit y a update).
     */
    public function update(User $user, Reservation $reservation): Response
    {
        if (! $this->ownerOrAdmin($user, $reservation)) {
            return Response::deny('Solo puedes modificar tus propias solicitudes.');
        }

        return $reservation->isPending()
            ? Response::allow()
            : Response::deny('Solo se puede editar una solicitud pendiente.');
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        return $this->ownerOrAdmin($user, $reservation);
    }

    /**
     * Aprobar o rechazar: administrador y solicitud pendiente.
     */
    public function review(User $user, Reservation $reservation): Response
    {
        if (! $user->isAdmin()) {
            return Response::deny('Solo el administrador aprueba o rechaza reservas.');
        }

        return $reservation->isPending()
            ? Response::allow()
            : Response::deny('Esta solicitud ya fue revisada.');
    }

    /**
     * Cancelar libera el espacio: solo reservas pendientes o aprobadas.
     */
    public function cancel(User $user, Reservation $reservation): Response
    {
        if (! $this->ownerOrAdmin($user, $reservation)) {
            return Response::deny('Solo puedes cancelar tus propias reservas.');
        }

        return $reservation->isActive()
            ? Response::allow()
            : Response::deny('Solo se puede cancelar una reserva pendiente o aprobada.');
    }

    private function ownerOrAdmin(User $user, Reservation $reservation): bool
    {
        return $user->isAdmin() || $reservation->user_id === $user->id;
    }
}
