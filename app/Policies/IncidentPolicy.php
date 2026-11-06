<?php

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Auth\Access\Response;

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

    public function update(User $user, Incident $incident): Response
    {
        if (! $this->ownerOrAdmin($user, $incident)) {
            return Response::deny('Solo puedes modificar tus propios reportes.');
        }

        return $incident->isResolved()
            ? Response::deny('Un incidente resuelto ya no se puede editar.')
            : Response::allow();
    }

    public function delete(User $user, Incident $incident): bool
    {
        return $this->ownerOrAdmin($user, $incident);
    }

    /**
     * Asignar responsable y registrar la solucion.
     */
    public function manage(User $user, Incident $incident): Response
    {
        if (! $user->isAdmin()) {
            return Response::deny('Solo el administrador gestiona la solución de los incidentes.');
        }

        return $incident->isResolved()
            ? Response::deny('Este incidente ya está resuelto.')
            : Response::allow();
    }

    private function ownerOrAdmin(User $user, Incident $incident): bool
    {
        return $user->isAdmin() || $incident->user_id === $user->id;
    }
}
