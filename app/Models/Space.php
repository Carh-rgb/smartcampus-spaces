<?php

namespace App\Models;

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
    /** @use HasFactory<\Database\Factories\SpaceFactory> */
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
}
