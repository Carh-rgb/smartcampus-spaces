<?php

namespace App\Support;

/**
 * Etiquetas en espanol de los valores enum que guarda la base de datos.
 * Las vistas las usan tanto para pintar los select de los formularios
 * como para mostrar el estado de un registro en los listados.
 */
class Catalog
{
    public const SPACE_TYPES = [
        'classroom' => 'Aula',
        'lab' => 'Laboratorio',
        'auditorium' => 'Auditorio',
        'meeting_room' => 'Sala de reuniones',
    ];

    public const SPACE_STATUSES = [
        'available' => 'Disponible',
        'maintenance' => 'En mantenimiento',
        'inactive' => 'Inactivo',
    ];

    public const RESOURCE_STATUSES = [
        'operational' => 'Operativo',
        'maintenance' => 'En mantenimiento',
        'out_of_service' => 'Fuera de servicio',
    ];

    public const RESERVATION_STATUSES = [
        'pending' => 'Pendiente',
        'approved' => 'Aprobada',
        'rejected' => 'Rechazada',
        'cancelled' => 'Cancelada',
    ];

    public const INCIDENT_STATUSES = [
        'open' => 'Abierto',
        'in_progress' => 'En proceso',
        'resolved' => 'Resuelto',
    ];

    public const PRIORITIES = [
        'high' => 'Alta',
        'medium' => 'Media',
        'low' => 'Baja',
    ];

    public const ROLES = [
        'admin' => 'Administrador',
        'teacher' => 'Docente',
        'student' => 'Estudiante',
    ];

    /**
     * Devuelve la etiqueta de un valor, o el valor mismo si no esta en el mapa.
     */
    public static function label(array $map, ?string $value): string
    {
        return $map[$value] ?? (string) $value;
    }
}
