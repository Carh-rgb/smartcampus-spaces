<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Consulta publica de espacios en formato JSON. Es la version desacoplada
 * del listado: sirve para una app movil o un tablero externo que solo
 * necesite leer la disponibilidad del campus.
 */
class SpaceController extends Controller
{
    /**
     * GET /api/spaces - Listado de espacios con sus recursos.
     */
    public function index(Request $request): JsonResponse
    {
        $spaces = Space::query()
            ->with('resources')
            ->when($request->filled('building'), fn ($query) => $query->where('building', $request->building))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->type))
            ->orderBy('name')
            ->get()
            ->map(fn (Space $space) => $this->transform($space));

        return response()->json([
            'data' => $spaces,
            'meta' => ['total' => $spaces->count()],
        ]);
    }

    /**
     * GET /api/spaces/{space} - Ficha de un espacio.
     */
    public function show(Space $space): JsonResponse
    {
        $space->load('resources');

        return response()->json(['data' => $this->transform($space)]);
    }

    /**
     * GET /api/spaces/{space}/availability?date=YYYY-MM-DD
     * Franjas ya ocupadas del espacio en la fecha consultada.
     */
    public function availability(Request $request, Space $space): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $ocupadas = Reservation::query()
            ->where('space_id', $space->id)
            ->whereDate('date', $validated['date'])
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('start_time')
            ->get(['id', 'start_time', 'end_time', 'status', 'purpose']);

        return response()->json([
            'data' => [
                'space' => ['id' => $space->id, 'name' => $space->name],
                'date' => $validated['date'],
                'is_bookable' => $space->status === 'available',
                'busy_slots' => $ocupadas,
            ],
        ]);
    }

    /**
     * Forma del espacio en la respuesta JSON.
     */
    private function transform(Space $space): array
    {
        return [
            'id' => $space->id,
            'name' => $space->name,
            'building' => $space->building,
            'capacity' => $space->capacity,
            'type' => $space->type,
            'status' => $space->status,
            'resources' => $space->resources->map(fn ($resource) => [
                'id' => $resource->id,
                'name' => $resource->name,
                'quantity' => $resource->pivot->quantity,
                'status' => $resource->status,
            ])->values(),
        ];
    }
}
