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
 * Operación DELETE de cada entidad: borrado lógico, redirección con
 * mensaje de confirmación y que el historial siga visible sin errores.
 */
class EliminacionTest extends TestCase
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

    public function test_eliminar_un_espacio_con_historial_no_rompe_las_pantallas(): void
    {
        $reservation = Reservation::factory()->create([
            'space_id' => $this->space->id,
            'user_id' => $this->student->id,
            'status' => 'cancelled',
        ]);
        Incident::factory()->create(['reservation_id' => $reservation->id]);

        $this->actingAs($this->admin)
            ->delete(route('spaces.destroy', $this->space))
            ->assertRedirect(route('spaces.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($this->space);

        $this->get(route('reservations.index'))->assertOk()->assertSee('(espacio eliminado)');
        $this->get(route('reservations.show', $reservation))->assertOk()->assertSee('(espacio eliminado)');
        $this->get(route('incidents.index'))->assertOk();
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_no_se_elimina_un_espacio_con_reservas_activas(): void
    {
        Reservation::factory()->create(['space_id' => $this->space->id, 'status' => 'approved']);

        $this->actingAs($this->admin)
            ->delete(route('spaces.destroy', $this->space))
            ->assertRedirect(route('spaces.index'))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($this->space);
    }

    public function test_la_reserva_se_elimina_desde_su_ficha(): void
    {
        $reservation = Reservation::factory()->create([
            'space_id' => $this->space->id,
            'user_id' => $this->student->id,
            'status' => 'pending',
        ]);
        $incident = Incident::factory()->create([
            'reservation_id' => $reservation->id,
            'user_id' => $this->student->id,
        ]);

        $this->actingAs($this->student)
            ->get(route('reservations.show', $reservation))
            ->assertSee(route('reservations.destroy', $reservation));

        $this->delete(route('reservations.destroy', $reservation))
            ->assertRedirect(route('reservations.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($reservation);

        // El incidente conserva su historial aunque la reserva ya no exista.
        $this->get(route('incidents.show', $incident))->assertOk()->assertSee('Reserva eliminada');
        $this->get(route('incidents.index'))->assertOk();
    }

    public function test_nadie_elimina_una_reserva_ajena(): void
    {
        $ajena = Reservation::factory()->create(['user_id' => $this->admin->id, 'space_id' => $this->space->id]);

        $this->actingAs($this->student)
            ->delete(route('reservations.destroy', $ajena))
            ->assertForbidden();

        $this->assertNotSoftDeleted($ajena);
    }

    public function test_espacios_y_recursos_se_eliminan_desde_su_ficha(): void
    {
        $resource = Resource::factory()->create();

        $this->actingAs($this->admin);

        $this->get(route('spaces.show', $this->space))->assertSee(route('spaces.destroy', $this->space));
        $this->get(route('resources.show', $resource))->assertSee(route('resources.destroy', $resource));

        $this->delete(route('resources.destroy', $resource))
            ->assertRedirect(route('resources.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($resource);
    }

    public function test_la_confirmacion_soporta_nombres_con_apostrofo(): void
    {
        $space = Space::factory()->create(['name' => "Sala O'Connor"]);

        $html = $this->actingAs($this->admin)->get(route('spaces.index'))->getContent();

        // El apóstrofo llega escapado dentro del JavaScript de confirmación.
        $this->assertStringContainsString('Sala O\\u0027Connor', $html);
        $this->assertStringNotContainsString("confirm('¿Eliminar el espacio Sala O'Connor", $html);
    }
}
