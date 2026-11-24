<?php

namespace App\Http\Controllers\Salud;

use App\Http\Controllers\Controller;
use App\Salud\SaludEstablecimiento;

class SaludWebController extends Controller
{
    const CATEGORIAS = [
        'medicina_general' => 'Medicina general',
        'odontologia'      => 'Odontología',
        'farmacia'         => 'Farmacia',
        'vacunacion'       => 'Vacunación',
        'fisioterapia'     => 'Fisioterapia',
        'laboratorio'      => 'Laboratorio',
        'especialidades'   => 'Especialidades',
        'emergencias'      => 'Emergencias',
        'programas'        => 'Programas',
        'fonoaudiologia'   => 'Fonoaudiología',
        'trabajo_social'   => 'Trabajo social',
        'enfermeria'       => 'Enfermería',
        'imagenologia'     => 'Imagenología',
        'telesalud'        => 'Telesalud',
        'otro'             => 'Otros servicios',
    ];

    public function index()
    {
        $establecimientos = SaludEstablecimiento::activos()
            ->with(['foto', 'telefonos', 'servicios', 'horarios'])
            ->ordenados()
            ->get();

        $abiertos = 0;
        $servicios = [];

        foreach ($establecimientos as $establecimiento) {
            $establecimiento->abierto = $establecimiento->estaAbierto();

            if ($establecimiento->abierto) {
                $abiertos++;
            }

            foreach ($establecimiento->servicios as $servicio) {
                $categoria = $servicio->categoria ?: 'otro';
                $servicios[$categoria] = self::CATEGORIAS[$categoria] ?? ucfirst($categoria);
            }
        }

        ksort($servicios);

        $total24h = $establecimientos->filter(function ($establecimiento) {
            return $establecimiento->atiende_24h;
        })->count();

        return view('client.salud.index', compact(
            'establecimientos',
            'abiertos',
            'servicios',
            'total24h'
        ));
    }

    public function show($slug)
    {
        $establecimiento = SaludEstablecimiento::activos()
            ->where('slug', $slug)
            ->with([
                'foto', 'telefonos', 'horarios', 'servicios', 'secciones',
                'cronogramasFilas',
                'cronogramasMensuales' => function ($query) {
                    $query->vigentes()
                        ->orderBy('mes', 'desc')
                        ->orderBy('id', 'desc');
                },
                'cronogramasMensuales.archivo',
                'cronogramasMensuales.servicio',
            ])
            ->firstOrFail();

        $establecimiento->abierto = $establecimiento->estaAbierto();
        $categorias = self::CATEGORIAS;

        return view('client.salud.show', compact('establecimiento', 'categorias'));
    }
}
