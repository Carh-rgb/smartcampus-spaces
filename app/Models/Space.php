<?php

namespace App\Models;

use Database\Factories\SpaceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Espacio fisico del campus (aula, laboratorio, auditorio o sala).
 * Modelo Space -> tabla spaces.
 */
class Space extends Model
{
    /** @use HasFactory<SpaceFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'building',
        'capacity',
        'type',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    /**
     * Relacion 1:N -> un espacio puede tener muchas reservas.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Relacion N:M -> un espacio contiene muchos recursos (tabla resource_space).
     */
    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class)
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes de consulta
    |--------------------------------------------------------------------------
    */

    /**
     * Filtros del listado: bloque, estado y tipo (si vienen en la peticion).
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['building'] ?? null, fn ($q, $building) => $q->where('building', $building))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type));
    }

    /**
     * Espacios que se pueden reservar (los que estan en mantenimiento no).
     */
    public function scopeBookable(Builder $query): Builder
    {
        return $query->where('status', 'available')->orderBy('building')->orderBy('name');
    }

    /**
     * Numero de reservas pendientes o aprobadas: impide borrar el espacio.
     */
    public function activeReservationsCount(): int
    {
        return $this->reservations()->active()->count();
    }
}
