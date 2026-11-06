<?php

namespace App\Policies;

use App\Models\Resource;
use App\Models\User;

/**
 * Permisos sobre el catalogo de recursos: todos lo consultan,
 * solo el administrador lo gestiona.
 */
class ResourcePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Resource $resource): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Resource $resource): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Resource $resource): bool
    {
        return $user->isAdmin();
    }
}
