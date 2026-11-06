<?php

namespace App\Policies;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Auth\Access\Response;

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

    public function create(User $user): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Solo el administrador puede gestionar el catálogo de recursos.');
    }

    public function update(User $user, Resource $resource): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Solo el administrador puede gestionar el catálogo de recursos.');
    }

    public function delete(User $user, Resource $resource): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Solo el administrador puede gestionar el catálogo de recursos.');
    }
}
