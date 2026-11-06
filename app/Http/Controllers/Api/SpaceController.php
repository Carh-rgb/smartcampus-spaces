<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SpaceResource;
use App\Models\Space;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Consulta publica de espacios en formato JSON. Es la version desacoplada
 * del listado: sirve para una app movil o un tablero externo que solo
 * necesite leer la disponibilidad del campus. La forma de cada espacio
 * la define el API Resource App\Http\Resources\SpaceResource.
 */
class SpaceController extends Controller
{
    /**
     * GET /api/v1/spaces - Listado de espacios con sus recursos.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $spaces = Space::query()
            ->with('resources')
            ->filter($request->only('building', 'type'))
            ->orderBy('name')
            ->get();

        return SpaceResource::collection($spaces)->additional([
            'meta' => ['total' => $spaces->count()],
        ]);
    }

    /**
     * GET /api/v1/spaces/{space} - Ficha de un espacio.
     */
    public function show(Space $space): SpaceResource
    {
        return new SpaceResource($space->load('resources'));
    }

    /**
     * GET /api/v1/spaces/{space}/availability?date=YYYY-MM-DD
     * Franjas ya ocupadas del espacio en la fecha consultada.
     */
    public function availability(Request $request, Space $space): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $ocupadas = $space->reservations()
            ->active()
            ->whereDate('date', $validated['date'])
            ->orderBy('start_time')
            ->get(['id', 'start_time', 'end_time', 'status']);

        return response()->json([
            'data' => [
                'space' => ['id' => $space->id, 'name' => $space->name],
                'date' => $validated['date'],
                'is_bookable' => $space->status === 'available',
                'busy_slots' => $ocupadas,
            ],
        ]);
    }
}
