<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Reservation;
use App\Models\Resource;
use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Flujo CRUD completo de cada entidad con Eloquent: POST (crear),
 * GET (consultar), PUT (editar) y DELETE (eliminar), con redirección
 * a una ruta nombrada y notificación toast de confirmación.
 */
class CrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->student = User::factory()->student()->create();
    }

    public function test_crud_completo_de_espacios(): void
    {
        $this->actingAs($this->admin);

        $datos = ['name' => 'Laboratorio de Redes', 'building' => 'Bloque D', 'capacity' => 24, 'type' => 'lab', 'status' => 'available', 'description' => 'Equipos Cisco'];

        // POST: crear
        $this->post(route('spaces.store'), $datos)
            ->assertRedirect(route('spaces.show', $space = Space::firstWhere('name', 'Laboratorio de Redes')))
            ->assertSessionHas('success', 'Espacio creado correctamente.');
        $this->assertDatabaseHas('spaces', $datos);

        // GET: consultar (el toast se muestra en la ficha)
        $this->get(route('spaces.show', $space))
            ->assertOk()
            ->assertSee('Laboratorio de Redes')
            ->assertSee('data-toast="success"', false)
            ->assertSee('Espacio creado correctamente.');

        // PUT: editar
        $this->put(route('spaces.update', $space), ['capacity' => 30] + $datos)
            ->assertRedirect(route('spaces.show', $space))
            ->assertSessionHas('success', 'Espacio actualizado correctamente.');
        $this->assertSame(30, $space->fresh()->capacity);

        // DELETE: eliminar (borrado lógico)
        $this->delete(route('spaces.destroy', $space))
            ->assertRedirect(route('spaces.index'))
            ->assertSessionHas('success', 'Se eliminó el espacio Laboratorio de Redes.');
        $this->assertSoftDeleted($space);
        $this->get(route('spaces.index'))->assertDontSee('Laboratorio de Redes</a>', false);
    }

    public function test_crud_completo_de_recursos_y_asignacion_a_un_espacio(): void
    {
        $this->actingAs($this->admin);
        $space = Space::factory()->create();

        $this->post(route('resources.store'), ['name' => 'Parlante Bluetooth', 'status' => 'operational'])
            ->assertRedirect(route('resources.index'))
            ->assertSessionHas('success', 'Recurso agregado al catálogo.');
        $resource = Resource::firstWhere('name', 'Parlante Bluetooth');
        $this->assertNotNull($resource);

        $this->put(route('resources.update', $resource), ['name' => 'Parlante Bluetooth', 'status' => 'maintenance', 'description' => 'Batería dañada'])
            ->assertRedirect(route('resources.index'))
            ->assertSessionHas('success', 'Recurso actualizado.');
        $this->assertSame('maintenance', $resource->fresh()->status);

        // Tabla pivote: asignar (POST) y retirar (DELETE)
        $this->post(route('spaces.resources.store', $space), ['resource_id' => $resource->id, 'quantity' => 3])
            ->assertRedirect(route('spaces.show', $space))
            ->assertSessionHas('success', 'Recurso asignado al espacio.');
        $this->assertDatabaseHas('resource_space', ['space_id' => $space->id, 'resource_id' => $resource->id, 'quantity' => 3]);

        $this->delete(route('resources.destroy', $resource))
            ->assertSessionHas('error');
        $this->assertNotSoftDeleted($resource);

        $this->delete(route('spaces.resources.destroy', [$space, $resource]))
            ->assertRedirect(route('spaces.show', $space))
            ->assertSessionHas('success', 'Recurso retirado del espacio.');

        $this->delete(route('resources.destroy', $resource))
            ->assertRedirect(route('resources.index'))
            ->assertSessionHas('success', 'Se eliminó Parlante Bluetooth del catálogo.');
        $this->assertSoftDeleted($resource);
    }

    public function test_crud_completo_de_reservas_con_sus_cambios_de_estado(): void
    {
        $space = Space::factory()->create(['status' => 'available']);
        $fecha = now()->addDays(3)->toDateString();

        $this->actingAs($this->student)
            ->post(route('reservations.store'), ['space_id' => $space->id, 'date' => $fecha, 'start_time' => '08:00', 'end_time' => '10:00', 'purpose' => 'Clase de Bases de Datos'])
            ->assertSessionHas('success', 'Solicitud enviada. Queda pendiente de aprobación.');
        $reservation = Reservation::firstWhere('purpose', 'Clase de Bases de Datos');
        $this->assertSame('pending', $reservation->status);
        $this->assertSame($this->student->id, $reservation->user_id);

        $this->put(route('reservations.update', $reservation), ['space_id' => $space->id, 'date' => $fecha, 'start_time' => '09:00', 'end_time' => '11:00', 'purpose' => 'Clase de Bases de Datos'])
            ->assertRedirect(route('reservations.show', $reservation))
            ->assertSessionHas('success', 'Solicitud actualizada.');
        $this->assertSame('09:00', substr($reservation->fresh()->start_time, 0, 5));

        // PATCH: el administrador decide y la acción vuelve al detalle
        $this->actingAs($this->admin)
            ->patch(route('reservations.approve', $reservation), ['review_notes' => 'Aprobada'])
            ->assertRedirect(route('reservations.show', $reservation))
            ->assertSessionHas('success');
        $this->assertSame('approved', $reservation->fresh()->status);

        $this->actingAs($this->student)
            ->patch(route('reservations.cancel', $reservation))
            ->assertRedirect(route('reservations.show', $reservation));
        $this->assertSame('cancelled', $reservation->fresh()->status);

        $this->delete(route('reservations.destroy', $reservation))
            ->assertRedirect(route('reservations.index'))
            ->assertSessionHas('success', 'Solicitud eliminada.');
        $this->assertSoftDeleted($reservation);
    }

    public function test_rechazo_guarda_el_motivo(): void
    {
        $reservation = Reservation::factory()->create(['status' => 'pending']);

        $this->actingAs($this->admin)
            ->patch(route('reservations.reject', $reservation), ['rejection_reason' => 'El auditorio está reservado para grados'])
            ->assertRedirect(route('reservations.show', $reservation));

        $reservation->refresh();
        $this->assertSame('rejected', $reservation->status);
        $this->assertSame('El auditorio está reservado para grados', $reservation->review_notes);
        $this->assertSame($this->admin->id, $reservation->approved_by);
    }

    public function test_crud_completo_de_incidentes(): void
    {
        $reservation = Reservation::factory()->create(['user_id' => $this->student->id, 'status' => 'approved']);

        $this->actingAs($this->student)
            ->post(route('incidents.store'), ['reservation_id' => $reservation->id, 'title' => 'Proyector sin imagen', 'description' => 'No detecta el HDMI', 'priority' => 'high'])
            ->assertSessionHas('success', 'Incidente reportado. Queda abierto para su revisión.');
        $incident = Incident::firstWhere('title', 'Proyector sin imagen');
        $this->assertSame('open', $incident->status);

        $this->put(route('incidents.update', $incident), ['reservation_id' => $reservation->id, 'title' => 'Proyector sin imagen', 'description' => 'No detecta el HDMI ni el VGA', 'priority' => 'high'])
            ->assertRedirect(route('incidents.show', $incident))
            ->assertSessionHas('success', 'Reporte actualizado.');
        $this->assertSame('No detecta el HDMI ni el VGA', $incident->fresh()->description);

        $this->actingAs($this->admin)
            ->patch(route('incidents.assign', $incident), ['assigned_to' => $this->admin->id])
            ->assertRedirect(route('incidents.show', $incident));
        $this->assertSame('in_progress', $incident->fresh()->status);

        $this->patch(route('incidents.resolve', $incident), ['solution' => 'Se cambió el cable HDMI'])
            ->assertRedirect(route('incidents.show', $incident))
            ->assertSessionHas('success', 'Incidente resuelto. Se registró la solución aplicada.');
        $this->assertSame('resolved', $incident->fresh()->status);

        $this->delete(route('incidents.destroy', $incident))
            ->assertRedirect(route('incidents.index'))
            ->assertSessionHas('success', 'Reporte eliminado.');
        $this->assertSoftDeleted($incident);
    }

    public function test_los_errores_de_negocio_se_notifican_como_toast_de_error(): void
    {
        $space = Space::factory()->create();
        Reservation::factory()->create(['space_id' => $space->id, 'status' => 'pending']);

        $this->actingAs($this->admin)
            ->delete(route('spaces.destroy', $space))
            ->assertRedirect(route('spaces.index'));

        $this->get(route('spaces.index'))
            ->assertSee('data-toast="error"', false)
            ->assertSee('Cancélalas o recházalas primero.');
    }
}
