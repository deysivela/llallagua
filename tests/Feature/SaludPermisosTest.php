<?php

namespace Tests\Feature;

use App\Salud\SaludEstablecimiento;
use App\User;
use Caffeinated\Shinobi\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SaludPermisosTest extends TestCase
{
    use DatabaseTransactions;

    protected function usuario(array $roles = [])
    {
        $usuario = User::create([
            'user'     => 'salud_test_' . uniqid(),
            'password' => bcrypt('secret'),
            'statu'    => 'Activo',
        ]);

        foreach ($roles as $rol) {
            $usuario->assignRole($rol);
        }

        return $usuario;
    }

    protected function rolExiste($slug)
    {
        return Role::where('slug', $slug)->exists();
    }

    public function testUnInvitadoEsRedirigidoAlLogin()
    {
        $this->get(route('salud.index'))->assertRedirect(route('login'));
    }

    public function testUnUsuarioSinPermisosNoPuedeEntrarAlPanel()
    {
        $usuario = $this->usuario();

        $this->actingAs($usuario)
            ->get(route('salud.index'))
            ->assertStatus(403);
    }

    public function testUnAdministradorDeSaludPuedeVerTodosLosEstablecimientos()
    {
        if (!$this->rolExiste('admin_salud')) {
            $this->markTestSkipped('Ejecuta SaludPermissionsSeeder para probar este caso.');
        }

        $establecimiento = SaludEstablecimiento::orderBy('id')->first();
        if (!$establecimiento) {
            $this->markTestSkipped('Ejecuta SaludEstablecimientosSeeder para probar este caso.');
        }

        $usuario = $this->usuario(['admin_salud']);

        $this->actingAs($usuario)
            ->get(route('salud.index'))
            ->assertOk()
            ->assertSee($establecimiento->nombre);

        $this->actingAs($usuario)
            ->get(route('salud.edit', $establecimiento->id))
            ->assertOk();
    }

    public function testUnResponsableSoloVeYEditaSuEstablecimiento()
    {
        if (!$this->rolExiste('responsable_establecimiento')) {
            $this->markTestSkipped('Ejecuta SaludPermissionsSeeder para probar este caso.');
        }

        $establecimientos = SaludEstablecimiento::orderBy('id')->limit(2)->get();
        if ($establecimientos->count() < 2) {
            $this->markTestSkipped('Se necesitan al menos dos establecimientos para probar el alcance.');
        }

        $propio = $establecimientos[0];
        $ajeno = $establecimientos[1];

        $usuario = $this->usuario(['responsable_establecimiento']);
        $usuario->establecimientosSalud()->attach($propio->id);

        $this->actingAs($usuario)
            ->get(route('salud.index'))
            ->assertOk()
            ->assertSee($propio->nombre)
            ->assertDontSee($ajeno->nombre);

        $this->actingAs($usuario)
            ->get(route('salud.edit', $propio->id))
            ->assertOk();

        $this->actingAs($usuario)
            ->get(route('salud.edit', $ajeno->id))
            ->assertStatus(403);
    }

    public function testUnResponsableNoPuedeCrearEstablecimientos()
    {
        if (!$this->rolExiste('responsable_establecimiento')) {
            $this->markTestSkipped('Ejecuta SaludPermissionsSeeder para probar este caso.');
        }

        $usuario = $this->usuario(['responsable_establecimiento']);

        $this->actingAs($usuario)
            ->get(route('salud.create'))
            ->assertStatus(403);
    }
}
