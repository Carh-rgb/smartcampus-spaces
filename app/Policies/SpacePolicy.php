<?php

namespace App\Policies;

use App\Models\Space;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Permisos sobre los espacios del campus: todos los consultan,
 * solo el administrador los gestiona.
 */
class SpacePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Space $space): bool
    {
        return true;
    }

    public function create(User $user): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Solo el administrador puede gestionar los espacios.');
    }

    public function update(User $user, Space $space): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Solo el administrador puede gestionar los espacios.');
    }

    public function delete(User $user, Space $space): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Solo el administrador puede gestionar los espacios.');
    }

    /**
     * Asignar o retirar recursos del espacio (tabla pivote).
     */
    public function manageResources(User $user, Space $space): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Solo el administrador asigna recursos a un espacio.');
    }
}
