<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

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
    public function update(User $user, Reservation $reservation): bool
    {
        return $this->ownerOrAdmin($user, $reservation) && $reservation->isPending();
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        return $this->ownerOrAdmin($user, $reservation);
    }

    /**
     * Aprobar o rechazar: administrador y solicitud pendiente.
     */
    public function review(User $user, Reservation $reservation): bool
    {
        return $user->isAdmin() && $reservation->isPending();
    }

    /**
     * Cancelar libera el espacio: solo reservas pendientes o aprobadas.
     */
    public function cancel(User $user, Reservation $reservation): bool
    {
        return $this->ownerOrAdmin($user, $reservation) && $reservation->isActive();
    }

    private function ownerOrAdmin(User $user, Reservation $reservation): bool
    {
        return $user->isAdmin() || $reservation->user_id === $user->id;
    }
}
