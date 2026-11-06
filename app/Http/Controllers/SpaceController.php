<?php

namespace App\Http\Controllers;

use App\Http\Requests\SpaceRequest;
use App\Models\Resource;
use App\Models\Space;
use Illuminate\Http\Request;

/**
 * Gestion de los espacios fisicos del campus (aulas, laboratorios,
 * auditorios y salas de reunion). Controlador de tipo Resource: sus
 * siete metodos corresponden a las siete rutas de Route::resource.
 * Permisos: SpacePolicy. Validacion: SpaceRequest.
 */
class SpaceController extends Controller
{
    /**
     * GET /spaces - Listado de espacios con sus recursos asignados.
     */
    public function index(Request $request)
    {
        $spaces = Space::query()
            ->withCount('resources')
            ->filter($request->only('building', 'status'))
            ->orderBy('building')
            ->orderBy('name')
            ->get();

        $buildings = Space::query()->distinct()->orderBy('building')->pluck('building');

        return view('spaces.index', compact('spaces', 'buildings'));
    }

    /**
     * GET /spaces/create - Formulario de creacion.
     */
    public function create()
    {
        $this->authorize('create', Space::class);

        return view('spaces.create', ['space' => new Space]);
    }

    /**
     * POST /spaces - Guarda un espacio nuevo.
     */
    public function store(SpaceRequest $request)
    {
        $this->authorize('create', Space::class);

        $space = Space::create($request->validated());

        return to_route('spaces.show', $space)->with('success', 'Espacio creado correctamente.');
    }

    /**
     * GET /spaces/{space} - Ficha del espacio: recursos y proximas reservas.
     */
    public function show(Space $space)
    {
        $space->load([
            'resources',
            'reservations' => fn ($query) => $query->with('user')->orderBy('date')->orderBy('start_time'),
        ]);

        // Recursos que todavia no estan asignados a este espacio.
        $availableResources = Resource::query()
            ->whereNotIn('id', $space->resources->modelKeys())
            ->orderBy('name')
            ->get();

        return view('spaces.show', compact('space', 'availableResources'));
    }

    /**
     * GET /spaces/{space}/edit - Formulario de edicion.
     */
    public function edit(Space $space)
    {
        $this->authorize('update', $space);

        return view('spaces.edit', compact('space'));
    }

    /**
     * PUT/PATCH /spaces/{space} - Actualiza el espacio.
     */
    public function update(SpaceRequest $request, Space $space)
    {
        $this->authorize('update', $space);

        $space->update($request->validated());

        return to_route('spaces.show', $space)->with('success', 'Espacio actualizado correctamente.');
    }

    /**
     * DELETE /spaces/{space} - Borrado logico, salvo que tenga reservas activas.
     */
    public function destroy(Space $space)
    {
        $this->authorize('delete', $space);

        if ($activas = $space->activeReservationsCount()) {
            return to_route('spaces.index')
                ->with('error', "No se puede eliminar: el espacio tiene {$activas} reserva(s) activa(s).");
        }

        $space->delete();

        return to_route('spaces.index')->with('success', 'Espacio eliminado.');
    }
}
