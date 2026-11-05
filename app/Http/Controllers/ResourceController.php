<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Catalogo de recursos tecnologicos del campus (video beam, tableros,
 * aires acondicionados, etc.). Controlador de tipo Resource.
 */
class ResourceController extends Controller
{
    /**
     * GET /resources - Catalogo con el numero de espacios donde esta cada recurso.
     */
    public function index()
    {
        $resources = Resource::query()
            ->withCount('spaces')
            ->orderBy('name')
            ->get();

        return view('resources.index', compact('resources'));
    }

    /**
     * GET /resources/create - Formulario de creacion.
     */
    public function create()
    {
        $this->authorizeAdmin();

        return view('resources.create', ['resource' => new Resource]);
    }

    /**
     * POST /resources - Guarda un recurso nuevo.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        Resource::create($request->validate($this->rules()));

        return redirect()
            ->route('resources.index')
            ->with('success', 'Recurso agregado al catalogo.');
    }

    /**
     * GET /resources/{resource} - Detalle del recurso y en que espacios esta.
     */
    public function show(Resource $resource)
    {
        $resource->load('spaces');

        return view('resources.show', compact('resource'));
    }

    /**
     * GET /resources/{resource}/edit - Formulario de edicion.
     */
    public function edit(Resource $resource)
    {
        $this->authorizeAdmin();

        return view('resources.edit', compact('resource'));
    }

    /**
     * PUT/PATCH /resources/{resource} - Actualiza el recurso.
     */
    public function update(Request $request, Resource $resource)
    {
        $this->authorizeAdmin();

        $resource->update($request->validate($this->rules($resource)));

        return redirect()
            ->route('resources.index')
            ->with('success', 'Recurso actualizado.');
    }

    /**
     * DELETE /resources/{resource} - Borrado logico del recurso.
     */
    public function destroy(Resource $resource)
    {
        $this->authorizeAdmin();

        $asignados = $resource->spaces()->count();

        if ($asignados > 0) {
            return redirect()
                ->route('resources.index')
                ->with('error', "No se puede eliminar: el recurso esta asignado a {$asignados} espacio(s).");
        }

        $resource->delete();

        return redirect()
            ->route('resources.index')
            ->with('success', 'Recurso eliminado del catalogo.');
    }

    private function rules(?Resource $resource = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('resources', 'name')->ignore($resource)],
            'description' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['operational', 'maintenance', 'out_of_service'])],
        ];
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Solo el administrador puede gestionar el catalogo de recursos.');
    }
}
