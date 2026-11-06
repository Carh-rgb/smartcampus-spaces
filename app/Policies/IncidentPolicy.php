<?php

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;

/**
 * Permisos sobre los incidentes. Quien reporta puede corregir su reporte
 * mientras no este resuelto; el administrador asigna y cierra el caso.
 */
class IncidentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Incident $incident): bool
    {
        return $this->ownerOrAdmin($user, $incident);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Incident $incident): bool
    {
        return $this->ownerOrAdmin($user, $incident) && ! $incident->isResolved();
    }

    public function delete(User $user, Incident $incident): bool
    {
        return $this->ownerOrAdmin($user, $incident);
    }

    /**
     * Asignar responsable y registrar la solucion.
     */
    public function manage(User $user, Incident $incident): bool
    {
        return $user->isAdmin() && ! $incident->isResolved();
    }

    private function ownerOrAdmin(User $user, Incident $incident): bool
    {
        return $user->isAdmin() || $incident->user_id === $user->id;
    }
}
