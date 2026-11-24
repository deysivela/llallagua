<?php

namespace App\Http\Controllers\Salud;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaludCronogramaRequest;
use App\Salud\SaludArchivo;
use App\Salud\SaludArchivoManager;
use App\Salud\SaludAuditoria;
use App\Salud\SaludCronogramaMensual;
use App\Salud\SaludEstablecimiento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaludCronogramaController extends Controller
{
    protected $manager;

    public function __construct(SaludArchivoManager $manager)
    {
        $this->manager = $manager;
    }

    public function index(Request $request)
    {
        $query = SaludCronogramaMensual::with(['establecimiento', 'servicio', 'archivo'])
            ->where('eliminado', false);

        if (!$this->puedeAdministrarTodo()) {
            $query->whereIn('establecimiento_id', $this->idsPermitidos());
        }

        if ($request->filled('establecimiento_id')) {
            $query->where('establecimiento_id', $request->input('establecimiento_id'));
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        if ($request->filled('mes')) {
            try {
                $query->where('mes', Carbon::parse($request->input('mes') . '-01')->startOfMonth()->toDateString());
            } catch (\Exception $e) {
                // Ignorar filtro inválido.
            }
        }

        if ($request->filled('publicado')) {
            $query->where('publicado', (bool) $request->input('publicado'));
        }

        $cronogramas = $query->orderBy('mes', 'desc')->orderBy('id', 'desc')->get();
        $establecimientos = $this->establecimientosPermitidos();

        return view('serve.salud.cronogramas.index', compact('cronogramas', 'establecimientos'));
    }

    public function create(Request $request)
    {
        $establecimientos = $this->establecimientosPermitidos();

        if ($establecimientos->isEmpty()) {
            return redirect()->route('salud.cronogramas.index')
                ->with('error', 'No tienes establecimientos asignados para gestionar cronogramas.');
        }

        $cronograma = new SaludCronogramaMensual();
        $cronograma->mes = Carbon::now('America/La_Paz')->startOfMonth();
        $cronograma->establecimiento_id = $request->input('establecimiento_id');

        return view('serve.salud.cronogramas.create', compact('cronograma', 'establecimientos'));
    }

    public function store(SaludCronogramaRequest $request)
    {
        $establecimiento = SaludEstablecimiento::findOrFail($request->input('establecimiento_id'));
        $this->autorizarEstablecimiento($establecimiento);

        $archivo = $this->resolverArchivo($request);
        $mes = $this->normalizarMes($request->input('mes'));
        $tipo = $request->input('tipo') ?: 'cronograma_atencion';
        $fechaFin = $request->filled('fecha_fin') ? Carbon::parse($request->input('fecha_fin'))->toDateString() : null;

        $duplicado = $this->existeCronograma($establecimiento->id, $request->input('servicio_id'), $mes);

        $cronograma = DB::transaction(function () use ($request, $establecimiento, $archivo, $mes, $tipo, $fechaFin) {
            $cronograma = SaludCronogramaMensual::create([
                'establecimiento_id' => $establecimiento->id,
                'servicio_id'        => $request->filled('servicio_id') ? $request->input('servicio_id') : null,
                'tipo'               => $tipo,
                'mes'                => $mes,
                'fecha_fin'          => $fechaFin,
                'archivo_id'         => $archivo ? $archivo->id : null,
                'titulo'             => $request->input('titulo') ?: (SaludCronogramaMensual::TIPOS_CORTOS[$tipo] ?? 'Cronograma'),
                'nota'               => $request->input('nota'),
                'publicado'          => true,
                'subido_por'         => optional(auth()->user())->id,
                'eliminado'          => false,
            ]);

            SaludAuditoria::registrar('crear', 'cronograma', $cronograma->id, [], $cronograma->fresh()->toArray());

            return $cronograma;
        });

        $respuesta = redirect()->route('salud.cronogramas.index')
            ->with('success', 'Cronograma registrado correctamente.');

        if ($duplicado) {
            $respuesta->with('warning', 'Ya existía un cronograma para ese establecimiento, servicio y mes. Se registró uno nuevo; revisa si corresponde reemplazar el anterior.');
        }

        return $respuesta;
    }

    public function edit($id)
    {
        $cronograma = SaludCronogramaMensual::with(['archivo', 'establecimiento', 'servicio'])->findOrFail($id);
        $this->autorizarEstablecimiento($cronograma->establecimiento);

        $establecimientos = $this->establecimientosPermitidos();

        return view('serve.salud.cronogramas.edit', compact('cronograma', 'establecimientos'));
    }

    public function update(SaludCronogramaRequest $request, $id)
    {
        $cronograma = SaludCronogramaMensual::findOrFail($id);
        $this->autorizarEstablecimiento($cronograma->establecimiento);

        $establecimiento = SaludEstablecimiento::findOrFail($request->input('establecimiento_id'));
        $this->autorizarEstablecimiento($establecimiento);

        $archivo = $this->resolverArchivo($request, $cronograma->archivo_id);
        $mes = $this->normalizarMes($request->input('mes'));
        $tipo = $request->input('tipo') ?: ($cronograma->tipo ?: 'cronograma_atencion');
        $fechaFin = $request->filled('fecha_fin') ? Carbon::parse($request->input('fecha_fin'))->toDateString() : null;

        $duplicado = $this->existeCronograma(
            $establecimiento->id,
            $request->input('servicio_id'),
            $mes,
            $cronograma->id
        );

        DB::transaction(function () use ($request, $cronograma, $establecimiento, $archivo, $mes, $tipo, $fechaFin) {
            $antes = $cronograma->fresh()->toArray();

            $cronograma->update([
                'establecimiento_id' => $establecimiento->id,
                'servicio_id'        => $request->filled('servicio_id') ? $request->input('servicio_id') : null,
                'tipo'               => $tipo,
                'mes'                => $mes,
                'fecha_fin'          => $fechaFin,
                'archivo_id'         => $archivo ? $archivo->id : null,
                'titulo'             => $request->input('titulo') ?: (SaludCronogramaMensual::TIPOS_CORTOS[$tipo] ?? 'Cronograma'),
                'nota'               => $request->input('nota'),
                'publicado'          => true,
            ]);

            SaludAuditoria::registrar('editar', 'cronograma', $cronograma->id, $antes, $cronograma->fresh()->toArray());
        });

        $respuesta = redirect()->route('salud.cronogramas.index')
            ->with('success', 'Cronograma actualizado correctamente.');

        if ($duplicado) {
            $respuesta->with('warning', 'Ya existe otro cronograma para ese establecimiento, servicio y mes.');
        }

        return $respuesta;
    }

    public function destroy($id)
    {
        $cronograma = SaludCronogramaMensual::findOrFail($id);
        $this->autorizarEstablecimiento($cronograma->establecimiento);

        $antes = $cronograma->toArray();
        $cronograma->eliminado = true;
        $cronograma->publicado = false;
        $cronograma->save();

        SaludAuditoria::registrar('eliminar', 'cronograma', $cronograma->id, $antes, $cronograma->fresh()->toArray());

        return redirect()->route('salud.cronogramas.index')
            ->with('success', 'Cronograma eliminado. El archivo se conserva para auditoría.');
    }

    public function publish($id)
    {
        $cronograma = SaludCronogramaMensual::findOrFail($id);
        $this->autorizarEstablecimiento($cronograma->establecimiento);

        $antes = $cronograma->toArray();
        $cronograma->publicado = !$cronograma->publicado;
        $cronograma->save();

        SaludAuditoria::registrar(
            $cronograma->publicado ? 'publicar' : 'despublicar',
            'cronograma',
            $cronograma->id,
            $antes,
            $cronograma->fresh()->toArray()
        );

        return redirect()->back()->with(
            'success',
            $cronograma->publicado
                ? 'El cronograma ya es visible en el sitio público.'
                : 'El cronograma fue ocultado del sitio público.'
        );
    }

    protected function resolverArchivo(Request $request, $archivoActualId = null)
    {
        if ($request->hasFile('archivo')) {
            return $this->manager->guardar($request->file('archivo'), optional(auth()->user())->id);
        }

        if ($request->filled('archivo_id')) {
            return SaludArchivo::findOrFail($request->input('archivo_id'));
        }

        if ($archivoActualId) {
            return SaludArchivo::find($archivoActualId);
        }

        if ($request->filled('titulo') || $request->filled('nota')) {
            return null;
        }

        abort(422, 'Debes adjuntar un archivo (imagen/PDF) o ingresar el título y detalle de la actividad.');
    }

    protected function normalizarMes($mes)
    {
        return Carbon::parse($mes . '-01')->startOfMonth()->toDateString();
    }

    protected function existeCronograma($establecimientoId, $servicioId, $mes, $ignorarId = null)
    {
        $query = SaludCronogramaMensual::where('establecimiento_id', $establecimientoId)
            ->where('mes', $mes)
            ->where('eliminado', false);

        if ($servicioId) {
            $query->where('servicio_id', $servicioId);
        } else {
            $query->whereNull('servicio_id');
        }

        if ($ignorarId) {
            $query->where('id', '!=', $ignorarId);
        }

        return $query->exists();
    }

    protected function establecimientosPermitidos()
    {
        $query = SaludEstablecimiento::with(['servicios' => function ($q) {
            $q->orderBy('orden');
        }])->orderBy('nombre');

        if (!$this->puedeAdministrarTodo()) {
            $query->whereIn('id', $this->idsPermitidos());
        }

        return $query->get();
    }

    protected function idsPermitidos()
    {
        if (!$this->puedeAdministrarTodo()) {
            return auth()->user()
                ? auth()->user()->establecimientosSalud()->pluck('salud_establecimientos.id')->all()
                : [];
        }

        return SaludEstablecimiento::pluck('id')->all();
    }

    protected function autorizarEstablecimiento($establecimiento)
    {
        if (!$establecimiento) {
            abort(404);
        }

        if ($this->puedeAdministrarTodo()) {
            return;
        }

        $asignado = auth()->user()
            && auth()->user()->establecimientosSalud()
                ->where('salud_establecimientos.id', $establecimiento->id)
                ->exists();

        if (!$asignado) {
            abort(403, 'No tienes acceso a este establecimiento de salud.');
        }
    }

    protected function puedeAdministrarTodo()
    {
        $usuario = auth()->user();

        if (!$usuario) {
            return false;
        }

        return $usuario->can('salud.destroy') || $usuario->can('salud.auditoria');
    }
}
