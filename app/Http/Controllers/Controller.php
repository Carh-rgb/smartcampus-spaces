<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Controlador base. El trait AuthorizesRequests habilita $this->authorize(),
 * que delega los permisos en las Policies de app/Policies.
 */
abstract class Controller
{
    use AuthorizesRequests;
}
