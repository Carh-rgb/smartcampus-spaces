<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\Space;
use Illuminate\Http\Request;

/**
 * Asignacion de recursos a un espacio: es la tabla pivote resource_space
 * manejada desde la ficha del espacio. Ruta anidada spaces/{space}/resources.
 */
class SpaceResourceController extends Controller
{
    /**
     * POST /spaces/{space}/resources - Asigna un recurso con su cantidad.
     */
    public function store(Request $request, Space $space)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'resource_id' => ['required', 'exists:resources,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        // syncWithoutDetaching respeta el unique(resource_id, space_id)
        // de la migracion: si ya estaba asignado, solo cambia la cantidad.
        $space->resources()->syncWithoutDetaching([
            $validated['resource_id'] => ['quantity' => $validated['quantity']],
        ]);

        return back()->with('success', 'Recurso asignado al espacio.');
    }

    /**
     * DELETE /spaces/{space}/resources/{resource} - Quita la asignacion.
     */
    public function destroy(Space $space, Resource $resource)
    {
        $this->authorizeAdmin();

        $space->resources()->detach($resource->id);

        return back()->with('success', 'Recurso retirado del espacio.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Solo el administrador asigna recursos a un espacio.');
    }
}
