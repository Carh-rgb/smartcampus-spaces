<?php

namespace App\Policies;

use App\Models\Space;
use App\Models\User;

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

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Space $space): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Space $space): bool
    {
        return $user->isAdmin();
    }

    /**
     * Asignar o retirar recursos del espacio (tabla pivote).
     */
    public function manageResources(User $user, Space $space): bool
    {
        return $user->isAdmin();
    }
}
