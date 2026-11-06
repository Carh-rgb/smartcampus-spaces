<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResourceRequest;
use App\Models\Resource;

/**
 * Catalogo de recursos tecnologicos del campus (video beam, tableros,
 * aires acondicionados, etc.). Controlador de tipo Resource.
 * Permisos: ResourcePolicy. Validacion: ResourceRequest.
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
        $this->authorize('create', Resource::class);

        return view('resources.create', ['resource' => new Resource]);
    }

    /**
     * POST /resources - Guarda un recurso nuevo.
     */
    public function store(ResourceRequest $request)
    {
        $this->authorize('create', Resource::class);

        Resource::create($request->validated());

        return to_route('resources.index')->with('success', 'Recurso agregado al catalogo.');
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
        $this->authorize('update', $resource);

        return view('resources.edit', compact('resource'));
    }

    /**
     * PUT/PATCH /resources/{resource} - Actualiza el recurso.
     */
    public function update(ResourceRequest $request, Resource $resource)
    {
        $this->authorize('update', $resource);

        $resource->update($request->validated());

        return to_route('resources.index')->with('success', 'Recurso actualizado.');
    }

    /**
     * DELETE /resources/{resource} - Borrado logico, salvo que este asignado.
     */
    public function destroy(Resource $resource)
    {
        $this->authorize('delete', $resource);

        if ($asignados = $resource->spaces()->count()) {
            return to_route('resources.index')
                ->with('error', "No se puede eliminar: el recurso esta asignado a {$asignados} espacio(s).");
        }

        $resource->delete();

        return to_route('resources.index')->with('success', 'Recurso eliminado del catalogo.');
    }
}
