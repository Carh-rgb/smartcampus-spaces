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
 * Recorre la navegacion de la Actividad 3: cada ruta responde,
 * cada vista compila y las acciones escriben en la base de datos.
 */
class NavigationTest extends TestCase
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
        $this->space = Space::factory()->create(['status' => 'available']);
    }

    public function test_las_vistas_de_listado_responden(): void
    {
        $this->actingAs($this->admin);

        foreach (['dashboard', 'spaces.index', 'resources.index', 'reservations.index', 'incidents.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_el_administrador_abre_los_formularios(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('spaces.create'))->assertOk();
        $this->get(route('resources.create'))->assertOk();
        $this->get(route('reservations.create'))->assertOk();
        $this->get(route('incidents.create'))->assertOk();
    }

    public function test_el_solicitante_no_puede_crear_espacios(): void
    {
        $this->actingAs($this->student)
            ->get(route('spaces.create'))
            ->assertForbidden();
    }

    public function test_se_crea_un_espacio_y_se_ve_su_ficha(): void
    {
        $this->actingAs($this->admin)
            ->post(route('spaces.store'), [
                'name' => 'Aula 301',
                'building' => 'Bloque D',
                'capacity' => 35,
                'type' => 'classroom',
                'status' => 'available',
                'description' => null,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('spaces', ['name' => 'Aula 301']);

        $space = Space::where('name', 'Aula 301')->firstOrFail();
        $this->actingAs($this->admin)->get(route('spaces.show', $space))->assertOk();
    }

    public function test_se_asigna_un_recurso_al_espacio(): void
    {
        $resource = Resource::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('spaces.resources.store', $this->space), [
                'resource_id' => $resource->id,
                'quantity' => 4,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('resource_space', [
            'space_id' => $this->space->id,
            'resource_id' => $resource->id,
            'quantity' => 4,
        ]);
    }

    public function test_una_reserva_que_se_cruza_es_rechazada(): void
    {
        Reservation::factory()->create([
            'user_id' => $this->student->id,
            'space_id' => $this->space->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'status' => 'approved',
        ]);

        $this->actingAs($this->student)
            ->post(route('reservations.store'), [
                'space_id' => $this->space->id,
                'date' => now()->addDay()->toDateString(),
                'start_time' => '09:00',
                'end_time' => '11:00',
                'purpose' => 'Clase de prueba',
            ])
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('reservations', 1);
    }

    public function test_el_administrador_aprueba_una_reserva(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->student->id,
            'space_id' => $this->space->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)
            ->patch(route('reservations.approve', $reservation), ['review_notes' => 'Autorizado'])
            ->assertRedirect();

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'approved',
            'approved_by' => $this->admin->id,
        ]);
    }

    public function test_el_solicitante_no_aprueba_su_propia_reserva(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->student->id,
            'space_id' => $this->space->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->student)
            ->patch(route('reservations.approve', $reservation))
            ->assertForbidden();
    }

    public function test_se_resuelve_un_incidente(): void
    {
        $incident = Incident::factory()->create([
            'user_id' => $this->student->id,
            'status' => 'open',
        ]);

        $this->actingAs($this->admin)
            ->patch(route('incidents.resolve', $incident), ['solution' => 'Equipo reemplazado'])
            ->assertRedirect();

        $this->assertDatabaseHas('incidents', [
            'id' => $incident->id,
            'status' => 'resolved',
        ]);
    }

    public function test_la_api_devuelve_los_espacios_en_json(): void
    {
        $this->getJson('/api/v1/spaces')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name', 'building', 'capacity', 'resources']], 'meta' => ['total']]);

        $this->getJson(route('api.v1.spaces.availability', $this->space).'?date='.now()->toDateString())
            ->assertOk()
            ->assertJsonPath('data.space.id', $this->space->id);
    }
}
