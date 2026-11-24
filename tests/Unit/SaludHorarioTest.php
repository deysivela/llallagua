<?php

namespace Tests\Unit;

use App\Salud\SaludEstablecimiento;
use App\Salud\SaludHorario;
use Carbon\Carbon;
use Tests\TestCase;

class SaludHorarioTest extends TestCase
{
    protected function tearDown()
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    protected function establecimientoConHorarios(array $horarios, $atiende24h = false)
    {
        $establecimiento = new SaludEstablecimiento();
        $establecimiento->atiende_24h = $atiende24h;
        $establecimiento->setRelation('horarios', collect($horarios));

        return $establecimiento;
    }

    protected function horario(array $dias, $inicio, $fin)
    {
        $horario = new SaludHorario();
        $horario->dias = $dias;
        $horario->hora_inicio = $inicio;
        $horario->hora_fin = $fin;

        return $horario;
    }

    public function testUnEstablecimientoConAtencion24HorasSiempreEstaAbierto()
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 5, 3, 0, 0, 'America/La_Paz'));

        $establecimiento = $this->establecimientoConHorarios([], true);

        $this->assertTrue($establecimiento->estaAbierto());
    }

    public function testUnaFranjaNormalSoloAbreDentroDelHorario()
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 5, 10, 0, 0, 'America/La_Paz'));
        $dia = (int) Carbon::now('America/La_Paz')->dayOfWeekIso;

        $abierto = $this->establecimientoConHorarios([
            $this->horario([$dia], '08:00:00', '18:00:00'),
        ]);
        $this->assertTrue($abierto->estaAbierto());

        Carbon::setTestNow(Carbon::create(2026, 11, 5, 20, 0, 0, 'America/La_Paz'));
        $cerrado = $this->establecimientoConHorarios([
            $this->horario([$dia], '08:00:00', '18:00:00'),
        ]);
        $this->assertFalse($cerrado->estaAbierto());
    }

    public function testUnaFranjaQueCruzaLaMedianocheSigueAbiertaDeMadrugada()
    {
        $ahora = Carbon::create(2026, 11, 5, 2, 0, 0, 'America/La_Paz');
        Carbon::setTestNow($ahora);

        $dia = (int) $ahora->dayOfWeekIso;
        $ayer = $dia === 1 ? 7 : $dia - 1;

        $establecimiento = $this->establecimientoConHorarios([
            $this->horario([$ayer], '20:00:00', '08:00:00'),
        ]);

        $this->assertTrue($establecimiento->estaAbierto());
    }

    public function testUnaFranjaDeVeinticuatroHorasCubreElDiaIndicado()
    {
        $ahora = Carbon::create(2026, 11, 5, 13, 0, 0, 'America/La_Paz');
        Carbon::setTestNow($ahora);

        $dia = (int) $ahora->dayOfWeekIso;

        $abierto = $this->establecimientoConHorarios([
            $this->horario([$dia], '00:00:00', '00:00:00'),
        ]);
        $this->assertTrue($abierto->estaAbierto());

        $otroDia = $dia === 7 ? 1 : $dia + 1;
        $cerrado = $this->establecimientoConHorarios([
            $this->horario([$otroDia], '00:00:00', '00:00:00'),
        ]);
        $this->assertFalse($cerrado->estaAbierto());
    }

    public function testLosDiasSeMuestranEnCastellano()
    {
        $horario = $this->horario([5, 1, 3], '08:00:00', '18:00:00');

        $this->assertSame('Lun, Mié, Vie', $horario->diasTexto());
    }

    public function testLaUrlDelMapaUsaCoordenadasCuandoExisten()
    {
        $establecimiento = new SaludEstablecimiento();
        $establecimiento->nombre = 'Hospital Madre Obrera';
        $establecimiento->latitud = -18.4212;
        $establecimiento->longitud = -66.5847;

        $this->assertTrue(strpos($establecimiento->urlMapa(), '-18.4212,-66.5847') !== false);
    }

    public function testLaUrlDelMapaUsaElNombreCuandoNoHayCoordenadas()
    {
        $establecimiento = new SaludEstablecimiento();
        $establecimiento->nombre = 'Centro de Salud Catavi';

        $this->assertTrue(strpos($establecimiento->urlMapa(), urlencode('Centro de Salud Catavi')) !== false);
    }
}
