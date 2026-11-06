<?php

namespace App\Http\Resources;

use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Forma JSON de un espacio para la API (/api/v1/spaces).
 *
 * @mixin Space
 */
class SpaceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'building' => $this->building,
            'capacity' => $this->capacity,
            'type' => $this->type,
            'status' => $this->status,
            'resources' => $this->whenLoaded('resources', fn () => $this->resources->map(fn ($resource) => [
                'id' => $resource->id,
                'name' => $resource->name,
                'quantity' => $resource->pivot->quantity,
                'status' => $resource->status,
            ])->values()),
        ];
    }

    /**
     * Devuelve las tildes tal cual ("Cómputo") en lugar de "C\u00f3mputo".
     */
    public function jsonOptions(): int
    {
        return JSON_UNESCAPED_UNICODE;
    }
}
