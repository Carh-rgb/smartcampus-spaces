<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Resource;
use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Validación de formularios: el servidor rechaza los datos inválidos,
 * devuelve al formulario, muestra mensajes explicativos en español y
 * conserva lo que el usuario había escrito (old()).
 */
class ValidacionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $student;

    private Space $space;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->student = User::factory()->student()->create();
        $this->space = Space::factory()->create(['name' => 'Aula 101', 'status' => 'available']);
    }

    public function test_crear_espacio_invalido_muestra_errores_y_conserva_datos(): void
    {
        $this->actingAs($this->admin)
            ->from(route('spaces.create'))
            ->post(route('spaces.store'), [
                'name' => 'Aula 101',
                'building' => 'Bloque Z',
                'capacity' => 0,
                'type' => 'classroom',
                'status' => 'available',
            ])
            ->assertRedirect(route('spaces.create'))
            ->assertSessionHasErrors(['name', 'capacity'])
            ->assertSessionHasInput('building', 'Bloque Z');

        $this->get(route('spaces.create'))
            ->assertSee('Ya existe un espacio con ese nombre')
            ->assertSee('La capacidad debe ser de al menos 1 persona.')
            ->assertSee('value="Bloque Z"', false);

        $this->assertDatabaseCount('spaces', 1);
    }

    public function test_editar_espacio_invalido_no_modifica_el_registro(): void
    {
        $this->actingAs($this->admin)
            ->from(route('spaces.edit', $this->space))
            ->put(route('spaces.update', $this->space), [
                'name' => '',
                'building' => 'Bloque Nuevo',
                'capacity' => 'muchos',
                'type' => 'classroom',
                'status' => 'available',
            ])
            ->assertRedirect(route('spaces.edit', $this->space))
            ->assertSessionHasErrors(['name', 'capacity']);

        $this->get(route('spaces.edit', $this->space))
            ->assertSee('El campo nombre es obligatorio.')
            ->assertSee('La capacidad debe ser un número entero de personas.')
            ->assertSee('value="Bloque Nuevo"', false);

        $this->assertSame('Aula 101', $this->space->fresh()->name);
    }

    public function test_recurso_duplicado(): void
    {
        Resource::factory()->create(['name' => 'Video Beam']);

        $this->actingAs($this->admin)
            ->from(route('resources.create'))
            ->post(route('resources.store'), ['name' => 'Video Beam', 'status' => 'operational'])
            ->assertSessionHasErrors('name');

        $this->get(route('resources.create'))->assertSee('Ese recurso ya está en el catálogo.');
    }

    public function test_reserva_con_fecha_y_horas_invalidas(): void
    {
        $this->actingAs($this->student)
            ->from(route('reservations.create'))
            ->post(route('reservations.store'), [
                'space_id' => $this->space->id,
                'date' => now()->subDay()->toDateString(),
                'start_time' => '10:00',
                'end_time' => '09:00',
                'purpose' => 'Tutoría de cálculo',
            ])
            ->assertSessionHasErrors(['date', 'end_time'])
            ->assertSessionHasInput('purpose', 'Tutoría de cálculo');

        $this->get(route('reservations.create'))
            ->assertSee('La fecha no puede ser anterior a hoy.')
            ->assertSee('La hora de fin debe ser posterior a la hora de inicio.')
            ->assertSee('Tutoría de cálculo');

        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_no_se_reserva_un_espacio_en_mantenimiento(): void
    {
        $taller = Space::factory()->create(['status' => 'maintenance']);

        $this->actingAs($this->student)
            ->post(route('reservations.store'), [
                'space_id' => $taller->id,
                'date' => now()->addDay()->toDateString(),
                'start_time' => '08:00',
                'end_time' => '10:00',
                'purpose' => 'Clase',
            ])
            ->assertSessionHasErrors(['space_id' => 'El espacio seleccionado no está disponible para reservas.']);
    }

    public function test_el_cruce_de_horario_indica_la_franja_ocupada(): void
    {
        $fecha = now()->addDays(2)->toDateString();

        Reservation::factory()->create([
            'space_id' => $this->space->id,
            'date' => $fecha,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'status' => 'approved',
        ]);

        $this->actingAs($this->student)
            ->post(route('reservations.store'), [
                'space_id' => $this->space->id,
                'date' => $fecha,
                'start_time' => '09:00',
                'end_time' => '11:00',
                'purpose' => 'Clase',
            ])
            ->assertSessionHasErrors(['start_time' => 'El espacio ya tiene una reserva activa de 08:00 a 10:00 que se cruza con ese horario.']);
    }

    public function test_rechazar_sin_motivo_muestra_el_error_una_sola_vez(): void
    {
        $reservation = Reservation::factory()->create([
            'space_id' => $this->space->id,
            'user_id' => $this->student->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)
            ->from(route('reservations.show', $reservation))
            ->patch(route('reservations.reject', $reservation), ['rejection_reason' => ''])
            ->assertSessionHasErrors('rejection_reason');

        $html = $this->get(route('reservations.show', $reservation))->getContent();

        // Una vez en el resumen superior y otra debajo del campo de rechazo,
        // nunca debajo del campo de observación de la aprobación.
        $this->assertSame(2, substr_count($html, 'Escribe el motivo del rechazo'));
        $this->assertSame('pending', $reservation->fresh()->status);
    }

    public function test_incidente_solo_sobre_reservas_aprobadas_o_canceladas(): void
    {
        $pendiente = Reservation::factory()->create([
            'space_id' => $this->space->id,
            'user_id' => $this->student->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->student)
            ->post(route('incidents.store'), [
                'reservation_id' => $pendiente->id,
                'title' => 'Sin aire',
                'description' => 'No enfría',
                'priority' => 'low',
            ])
            ->assertSessionHasErrors(['reservation_id' => 'Solo se pueden reportar novedades sobre reservas aprobadas o canceladas.']);
    }

    public function test_asignar_recurso_con_cantidad_invalida(): void
    {
        $resource = Resource::factory()->create();

        $this->actingAs($this->admin)
            ->from(route('spaces.show', $this->space))
            ->post(route('spaces.resources.store', $this->space), ['resource_id' => $resource->id, 'quantity' => 0])
            ->assertRedirect(route('spaces.show', $this->space))
            ->assertSessionHasErrors(['quantity' => 'La cantidad debe ser de al menos 1 unidad.']);

        $this->assertDatabaseCount('resource_space', 0);
    }
}
