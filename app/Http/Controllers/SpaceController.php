<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Gestion de los espacios fisicos del campus (aulas, laboratorios,
 * auditorios y salas de reunion). Controlador de tipo Resource: sus
 * siete metodos corresponden a las siete rutas de Route::resource.
 */
class SpaceController extends Controller
{
    /**
     * GET /spaces - Listado de espacios con sus recursos asignados.
     */
    public function index(Request $request)
    {
        $spaces = Space::query()
            ->with('resources')
            ->when($request->filled('building'), fn ($query) => $query->where('building', $request->building))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
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
        $this->authorizeAdmin();

        return view('spaces.create', ['space' => new Space]);
    }

    /**
     * POST /spaces - Guarda un espacio nuevo.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate($this->rules());

        $space = Space::create($validated);

        return redirect()
            ->route('spaces.show', $space)
            ->with('success', 'Espacio creado correctamente.');
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
            ->whereNotIn('id', $space->resources->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('spaces.show', compact('space', 'availableResources'));
    }

    /**
     * GET /spaces/{space}/edit - Formulario de edicion.
     */
    public function edit(Space $space)
    {
        $this->authorizeAdmin();

        return view('spaces.edit', compact('space'));
    }

    /**
     * PUT/PATCH /spaces/{space} - Actualiza el espacio.
     */
    public function update(Request $request, Space $space)
    {
        $this->authorizeAdmin();

        $validated = $request->validate($this->rules($space));

        $space->update($validated);

        return redirect()
            ->route('spaces.show', $space)
            ->with('success', 'Espacio actualizado correctamente.');
    }

    /**
     * DELETE /spaces/{space} - Borrado logico, salvo que tenga reservas activas.
     */
    public function destroy(Space $space)
    {
        $this->authorizeAdmin();

        $activas = $space->reservations()
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        if ($activas > 0) {
            return redirect()
                ->route('spaces.index')
                ->with('error', "No se puede eliminar: el espacio tiene {$activas} reserva(s) activa(s).");
        }

        $space->delete();

        return redirect()
            ->route('spaces.index')
            ->with('success', 'Espacio eliminado.');
    }

    /**
     * Reglas de validacion compartidas por store() y update().
     */
    private function rules(?Space $space = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('spaces', 'name')->ignore($space)],
            'building' => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:1000'],
            'type' => ['required', Rule::in(['classroom', 'lab', 'auditorium', 'meeting_room'])],
            'status' => ['required', Rule::in(['available', 'maintenance', 'inactive'])],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Solo el administrador del campus administra los espacios.
     */
    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Solo el administrador puede gestionar los espacios.');
    }
}
