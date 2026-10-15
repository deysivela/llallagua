<?php

namespace Tests\Feature;

use App\Employee;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AjusteFotoCredencialTest extends TestCase
{
    // La prueba escribe en la base de datos real de desarrollo: con transaccion
    // todo se revierte solo, gane o pierda.
    use DatabaseTransactions;

    protected $rutaFoto;

    protected function employee()
    {
        $emp = Employee::orderBy('id')->first();
        if (!$emp) {
            $this->markTestSkipped('No hay funcionarios en la base de datos.');
        }
        return $emp;
    }

    protected function guardarOriginal($emp)
    {
        $this->rutaFoto = $emp->photo;
    }

    protected function restaurar($emp)
    {
        $emp->photo = $this->rutaFoto;
        $emp->save();
    }

    public function testGuardaElEncuadreEnLaColumnaPhoto()
    {
        $emp = $this->employee();
        $this->guardarOriginal($emp);

        $respuesta = $this->actingAs(User::first())
            ->postJson(route('credenciales.foto.ajuste'), [
                'employee_id' => $emp->id,
                'z' => 1.6, 'x' => 25, 'y' => -10,
            ])
            ->assertOk();

        $this->assertTrue($respuesta->json('ok'), 'la respuesta debe traer ok');

        $emp->refresh();
        $this->assertSame('server/imagenes/funcionarios/david.png?z=1.6&x=25&y=-10', $emp->photo);
        $this->assertEquals(['z' => 1.6, 'x' => 25.0, 'y' => -10.0], $emp->ajusteFoto());

        $this->restaurar($emp);
    }

    public function testAcotaElZoomYElDesplazamiento()
    {
        $emp = $this->employee();
        $this->guardarOriginal($emp);

        $this->actingAs(User::first())
            ->postJson(route('credenciales.foto.ajuste'), [
                'employee_id' => $emp->id,
                'z' => 99, 'x' => 99999, 'y' => -99999,
            ])
            ->assertOk();

        $emp->refresh();
        $ajuste = $emp->ajusteFoto();

        $this->assertEquals(4.0, $ajuste['z'], 'el zoom se topa en 4');
        // Con z=4 el margen es (480 * 3) / 2 = 720 de ancho y 645 de alto.
        $this->assertEquals(720.0, $ajuste['x']);
        $this->assertEquals(-645.0, $ajuste['y']);

        $this->restaurar($emp);
    }

    public function testElZoomNuncaBajaDeUnoParaNoDejarHuecos()
    {
        $emp = $this->employee();
        $this->guardarOriginal($emp);

        $this->actingAs(User::first())
            ->postJson(route('credenciales.foto.ajuste'), [
                'employee_id' => $emp->id, 'z' => 0.1, 'x' => 0, 'y' => 0,
            ])
            ->assertOk();

        $emp->refresh();
        $this->assertEquals(['z' => 1.0, 'x' => 0.0, 'y' => 0.0], $emp->ajusteFoto());
        // Si el encuadre vuelve al de siempre, la columna se deja pelada: la
        // foto no lleva "?z=1&x=0&y=0" colgando.
        $this->assertSame(
            $emp->rutaFoto(),
            $emp->photo,
            'con el encuadre por defecto la columna photo no debe llevar query'
        );

        $this->restaurar($emp);
    }

    public function testLaRutaParaElDiscoNoLlevaElEncuadre()
    {
        $emp = $this->employee();
        $this->guardarOriginal($emp);

        $emp->guardarAjusteFoto(['z' => 2.2, 'x' => 30, 'y' => -12]);

        $this->assertSame('server/imagenes/funcionarios/david.png', $emp->rutaFoto());
        $this->assertFalse(strpos($emp->rutaFoto(), '?') !== false, 'la ruta del disco no lleva query');

        $this->restaurar($emp);
    }

    public function testLaFotoPorDefectoSeReconoceAunConEncuadre()
    {
        $defecto = new Employee();
        $defecto->photo = 'server/imagenes/funcionarios/default-male.png?z=2.5&x=10&y=-10';

        $this->assertSame('server/imagenes/funcionarios/default-male.png', $defecto->rutaFoto());
        $this->assertTrue(
            $defecto->rutaFoto() === 'server/imagenes/funcionarios/default-male.png',
            'EmployeeController compara la ruta contra la foto por defecto con ==='
        );
    }

    public function testRechazaValoresQueNoSonNumericos()
    {
        $emp = $this->employee();

        $this->actingAs(User::first())
            ->postJson(route('credenciales.foto.ajuste'), [
                'employee_id' => $emp->id, 'z' => 'no-es-un-numero',
            ])
            ->assertStatus(422);
    }

    public function testPideEmpleadoValido()
    {
        $this->actingAs(User::first())
            ->postJson(route('credenciales.foto.ajuste'), [
                'employee_id' => 999999, 'z' => 2,
            ])
            ->assertStatus(422);
    }

    public function testLaColumnaNoSePasaDeLongitud()
    {
        $compuesta = Employee::componerFoto(
            'server/imagenes/funcionarios/abcdefghij0123456789abcdef.jpg',
            ['z' => 4, 'x' => 720, 'y' => 645]
        );

        $this->assertLessThanOrEqual(255, strlen($compuesta), 'la columna photo es VARCHAR(255)');
    }
}
