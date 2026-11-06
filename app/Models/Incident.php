<?php

namespace App\Models;

use Database\Factories\IncidentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Novedad o falla reportada sobre un espacio previamente reservado.
 * Modelo Incident -> tabla incidents.
 */
class Incident extends Model
{
    /** @use HasFactory<IncidentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reservation_id',
        'user_id',
        'title',
        'description',
        'priority',
        'status',
        'assigned_to',
        'solution',
        'reported_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Relacion N:1 -> el incidente pertenece a una reserva.
     */
    public function reservation(): BelongsTo
    {
        // withTrashed: el incidente conserva su reserva aunque esta se haya
        // eliminado (borrado logico), asi el historial sigue mostrandose.
        return $this->belongsTo(Reservation::class)->withTrashed();
    }

    /**
     * Relacion N:1 -> usuario que reporto el incidente.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Administrador o tecnico asignado a la solucion.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes de consulta
    |--------------------------------------------------------------------------
    */

    /**
     * El administrador ve todos los incidentes; el solicitante, los suyos.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->unless($user->isAdmin(), fn ($q) => $q->where('user_id', $user->id));
    }

    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    /**
     * Primero los de prioridad alta, luego media y baja.
     */
    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END");
    }

    /*
    |--------------------------------------------------------------------------
    | Reglas y transiciones de estado
    |--------------------------------------------------------------------------
    */

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function assignTo(int $userId): void
    {
        $this->update(['assigned_to' => $userId, 'status' => 'in_progress']);
    }

    public function resolve(string $solution, User $by): void
    {
        $this->update([
            'solution' => $solution,
            'status' => 'resolved',
            'resolved_at' => now(),
            'assigned_to' => $this->assigned_to ?? $by->id,
        ]);
    }
}
