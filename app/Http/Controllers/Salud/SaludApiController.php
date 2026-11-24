<?php

namespace App\Http\Controllers\Salud;

use App\Http\Controllers\Controller;
use App\Salud\SaludEstablecimiento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SaludApiController extends Controller
{
    const MESES = [
        1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
    ];

    public function index(Request $request)
    {
        $query = SaludEstablecimiento::activos()
            ->with(['foto', 'telefonos', 'servicios', 'horarios'])
            ->ordenados();

        if ($request->filled('q')) {
            $texto = trim($request->input('q'));
            $query->where(function ($q) use ($texto) {
                $q->where('nombre', 'like', '%' . $texto . '%')
                    ->orWhere('zona', 'like', '%' . $texto . '%')
                    ->orWhere('direccion', 'like', '%' . $texto . '%')
                    ->orWhere('responsable', 'like', '%' . $texto . '%');
            });
        }

        if ($request->filled('servicio')) {
            $servicio = trim($request->input('servicio'));
            $query->whereHas('servicios', function ($q) use ($servicio) {
                $q->where('categoria', $servicio)
                    ->orWhere('nombre', 'like', '%' . $servicio . '%');
            });
        }

        $establecimientos = $query->get();

        if ($request->filled('abierto')) {
            $abierto = in_array((string) $request->input('abierto'), ['1', 'true'], true);
            $establecimientos = $establecimientos->filter(function ($establecimiento) use ($abierto) {
                return $establecimiento->estaAbierto() === $abierto;
            })->values();
        }

        $porPagina = (int) $request->input('per_page', 12);
        $porPagina = max(1, min($porPagina, 50));
        $pagina = max(1, (int) $request->input('page', 1));
        $total = $establecimientos->count();

        $items = $establecimientos->slice(($pagina - 1) * $porPagina, $porPagina)->values();

        $paginador = new LengthAwarePaginator($items, $total, $porPagina, $pagina, [
            'path'  => url('/api/salud/establecimientos'),
            'query' => $request->query(),
        ]);

        $data = $items->map(function ($establecimiento) {
            return $this->formatear($establecimiento);
        })->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'total'        => $paginador->total(),
                'per_page'     => $paginador->perPage(),
                'current_page' => $paginador->currentPage(),
                'last_page'    => $paginador->lastPage(),
            ],
            'links' => [
                'first' => $paginador->url(1),
                'prev'  => $paginador->previousPageUrl(),
                'next'  => $paginador->nextPageUrl(),
                'last'  => $paginador->url($paginador->lastPage()),
            ],
        ]);
    }

    public function show($slug)
    {
        $establecimiento = SaludEstablecimiento::activos()
            ->where('slug', $slug)
            ->with([
                'foto', 'telefonos', 'horarios', 'servicios', 'secciones',
                'cronogramasFilas',
                'cronogramasMensuales' => function ($query) {
                    $query->where('publicado', true)
                        ->where('eliminado', false)
                        ->orderBy('mes', 'desc');
                },
                'cronogramasMensuales.archivo',
                'cronogramasMensuales.servicio',
            ])
            ->firstOrFail();

        return response()->json([
            'data' => $this->formatear($establecimiento, true),
        ]);
    }

    public function cronogramas($slug)
    {
        $establecimiento = SaludEstablecimiento::activos()
            ->where('slug', $slug)
            ->with([
                'cronogramasFilas',
                'cronogramasMensuales' => function ($query) {
                    $query->where('publicado', true)
                        ->where('eliminado', false)
                        ->orderBy('mes', 'desc');
                },
                'cronogramasMensuales.archivo',
                'cronogramasMensuales.servicio',
            ])
            ->firstOrFail();

        $cronogramas = $establecimiento->cronogramasMensuales->map(function ($cronograma) {
            return $this->formatearCronograma($cronograma);
        })->values();
        $filas = $this->formatearFilas($establecimiento);

        return response()->json([
            'establecimiento' => [
                'id'     => $establecimiento->id,
                'slug'   => $establecimiento->slug,
                'nombre' => $establecimiento->nombre,
            ],
            'cronogramas' => $cronogramas,
            'filas'       => $filas,
        ]);
    }

    protected function formatear(SaludEstablecimiento $establecimiento, $detalle = false)
    {
        $data = [
            'id'          => $establecimiento->id,
            'slug'        => $establecimiento->slug,
            'nombre'      => $establecimiento->nombre,
            'tipo'        => $establecimiento->tipo,
            'tipo_texto'  => $establecimiento->tipo_texto,
            'nivel'       => $establecimiento->nivel,
            'zona'        => $establecimiento->zona,
            'direccion'   => $establecimiento->direccion,
            'latitud'     => $establecimiento->latitud,
            'longitud'    => $establecimiento->longitud,
            'responsable' => $establecimiento->responsable,
            'descripcion' => $establecimiento->descripcion,
            'atiende_24h' => $establecimiento->atiende_24h,
            'abierto'     => $establecimiento->estaAbierto(),
            'foto'        => $establecimiento->foto ? $establecimiento->foto->urlPreferida() : null,
            'mapa'        => $establecimiento->urlMapa(),
            'telefonos'   => $establecimiento->telefonos->map(function ($telefono) {
                return [
                    'numero'   => $telefono->numero,
                    'etiqueta' => $telefono->etiqueta,
                ];
            })->values(),
            'servicios'   => $establecimiento->servicios->map(function ($servicio) {
                return [
                    'id'          => $servicio->id,
                    'nombre'      => $servicio->nombre,
                    'categoria'   => $servicio->categoria,
                    'descripcion' => $servicio->descripcion,
                ];
            })->values(),
            'horarios'    => $establecimiento->horarios->map(function ($horario) {
                return [
                    'dias'       => $horario->dias,
                    'dias_texto' => $horario->diasTexto(),
                    'hora_inicio' => substr($horario->hora_inicio, 0, 5),
                    'hora_fin'    => substr($horario->hora_fin, 0, 5),
                    'nota'        => $horario->nota,
                ];
            })->values(),
        ];

        if ($detalle) {
            $data['secciones'] = $establecimiento->secciones->map(function ($seccion) {
                return [
                    'titulo' => $seccion->titulo,
                    'texto'  => $seccion->texto,
                    'items'  => $seccion->items,
                ];
            })->values();
            $data['cronogramas'] = $establecimiento->cronogramasMensuales->map(function ($cronograma) {
                return $this->formatearCronograma($cronograma);
            })->values();
            $data['filas_cronograma'] = $this->formatearFilas($establecimiento);
        }

        return $data;
    }

    protected function formatearCronograma($cronograma)
    {
        return [
            'id'        => $cronograma->id,
            'mes'       => $cronograma->mes ? $cronograma->mes->format('Y-m-d') : null,
            'mes_texto' => $this->mesTexto($cronograma->mes),
            'titulo'    => $cronograma->titulo,
            'nota'      => $cronograma->nota,
            'servicio'  => $cronograma->servicio ? $cronograma->servicio->nombre : null,
            'archivo'   => $cronograma->archivo ? [
                'nombre'    => $cronograma->archivo->nombre_original,
                'mime'      => $cronograma->archivo->mime,
                'es_imagen' => $cronograma->archivo->esImagen(),
                'url'       => $cronograma->archivo->urlPreferida(),
                'thumb'     => $cronograma->archivo->urlThumb(),
            ] : null,
        ];
    }

    protected function formatearFilas($establecimiento)
    {
        return $establecimiento->cronogramasFilas->map(function ($fila) {
            return [
                'dia_o_fecha'   => $fila->dia_o_fecha,
                'servicio'      => $fila->servicio,
                'profesional'   => $fila->profesional,
                'horario'       => $fila->horario,
                'observaciones' => $fila->observaciones,
            ];
        })->values();
    }

    protected function mesTexto($mes)
    {
        if (!$mes instanceof Carbon) {
            if (!$mes) {
                return null;
            }
            $mes = Carbon::parse($mes);
        }

        return self::MESES[(int) $mes->format('n')] . ' ' . $mes->format('Y');
    }
}
