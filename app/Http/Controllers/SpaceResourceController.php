<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignResourceRequest;
use App\Models\Resource;
use App\Models\Space;

/**
 * Asignacion de recursos a un espacio: es la tabla pivote resource_space
 * manejada desde la ficha del espacio. Rutas anidadas spaces/{space}/resources.
 */
class SpaceResourceController extends Controller
{
    /**
     * POST /spaces/{space}/resources - Asigna un recurso con su cantidad.
     */
    public function store(AssignResourceRequest $request, Space $space)
    {
        $this->authorize('manageResources', $space);

        // syncWithoutDetaching respeta el unique(resource_id, space_id)
        // de la migracion: si ya estaba asignado, solo cambia la cantidad.
        $space->resources()->syncWithoutDetaching([
            $request->integer('resource_id') => ['quantity' => $request->integer('quantity')],
        ]);

        return back()->with('success', 'Recurso asignado al espacio.');
    }

    /**
     * DELETE /spaces/{space}/resources/{resource} - Quita la asignacion.
     */
    public function destroy(Space $space, Resource $resource)
    {
        $this->authorize('manageResources', $space);

        $space->resources()->detach($resource);

        return back()->with('success', 'Recurso retirado del espacio.');
    }
}
