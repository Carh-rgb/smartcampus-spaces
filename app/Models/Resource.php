<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Recurso tecnologico del campus (proyector, tablero inteligente, aire, etc.).
 * Modelo Resource -> tabla resources.
 */
class Resource extends Model
{
    /** @use HasFactory<\Database\Factories\ResourceFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    /**
     * Relacion N:M -> un recurso puede estar en muchos espacios.
     */
    public function spaces(): BelongsToMany
    {
        return $this->belongsToMany(Space::class)
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
