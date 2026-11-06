<?php

namespace App\Models;

use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Solicitud de uso de un espacio en una fecha y franja horaria.
 * Modelo Reservation -> tabla reservations.
 */
class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Estados que ocupan el espacio en su franja horaria.
     */
    public const ACTIVE_STATUSES = ['pending', 'approved'];

    protected $fillable = [
        'user_id',
        'space_id',
        'date',
        'start_time',
        'end_time',
        'purpose',
        'status',
        'approved_by',
        'reviewed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Relacion N:1 -> la reserva pertenece al usuario solicitante.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacion N:1 -> la reserva pertenece a un espacio.
     */
    public function space(): BelongsTo
    {
        // withTrashed: la reserva conserva su espacio aunque este se haya
        // eliminado (borrado logico), asi el historial sigue mostrandose.
        return $this->belongsTo(Space::class)->withTrashed();
    }

    /**
     * Administrador que aprobo o rechazo la solicitud.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relacion 1:N -> una reserva puede generar varios incidentes.
     */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes de consulta
    |--------------------------------------------------------------------------
    */

    /**
     * El administrador ve todas las reservas; el solicitante, solo las suyas.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->unless($user->isAdmin(), fn ($q) => $q->where('user_id', $user->id));
    }

    /**
     * Reservas que ocupan la franja: pendientes o aprobadas.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    /**
     * Reservas activas del mismo espacio y fecha cuyo horario se cruza con
     * [$start, $end). Se apoya en el indice (space_id, date, start_time).
     */
    public function scopeOverlapping(Builder $query, int $spaceId, string $date, string $start, string $end, ?int $ignoreId = null): Builder
    {
        return $query
            ->active()
            ->where('space_id', $spaceId)
            ->whereDate('date', $date)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start);
    }

    /**
     * Reservas sobre las que el usuario puede reportar un incidente.
     */
    public function scopeReportableBy(Builder $query, User $user): Builder
    {
        return $query->visibleTo($user)->whereIn('status', ['approved', 'cancelled']);
    }

    /*
    |--------------------------------------------------------------------------
    | Reglas y transiciones de estado
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function approve(User $admin, ?string $notes = null): void
    {
        $this->review('approved', $admin, $notes);
    }

    public function reject(User $admin, string $notes): void
    {
        $this->review('rejected', $admin, $notes);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    private function review(string $status, User $admin, ?string $notes): void
    {
        $this->update([
            'status' => $status,
            'approved_by' => $admin->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }
}
