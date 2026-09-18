<?php

namespace App\Models;

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
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory, SoftDeletes;

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
        return $this->belongsTo(Space::class);
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
}
