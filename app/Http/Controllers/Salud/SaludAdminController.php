<?php

namespace App\Http\Controllers\Salud;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaludEstablecimientoRequest;
use App\Salud\SaludAuditoria;
use App\Salud\SaludCronogramaFila;
use App\Salud\SaludEstablecimiento;
use App\Salud\SaludHorario;
use App\Salud\SaludSeccionDetalle;
use App\Salud\SaludServicio;
use App\Salud\SaludTelefono;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaludAdminController extends Controller
{
    const CATEGORIAS_SERVICIO = [
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
        'otro'             => 'Otro',
    ];

    public function index(Request $request)
    {
        $query = $this->consultaPermitida()
            ->with(['foto', 'telefonos', 'servicios', 'horarios'])
            ->withCount(['cronogramasMensuales as cronogramas_publicados_count' => function ($q) {
                $q->where('publicado', true)->where('eliminado', false);
            }]);

        if ($request->filled('q')) {
            $texto = trim($request->input('q'));
            $query->where(function ($q) use ($texto) {
                $q->where('nombre', 'like', '%' . $texto . '%')
                    ->orWhere('zona', 'like', '%' . $texto . '%')
                    ->orWhere('direccion', 'like', '%' . $texto . '%')
                    ->orWhere('responsable', 'like', '%' . $texto . '%');
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        if ($request->filled('activo')) {
            $query->where('activo', (bool) $request->input('activo'));
        }

        $establecimientos = $query->ordenados()->get();

        foreach ($establecimientos as $establecimiento) {
            $pendientes = $establecimiento->camposPendientes();
            $establecimiento->pendientes = $pendientes;
            $establecimiento->completitud = (int) round((4 - count($pendientes)) / 4 * 100);
        }

        return view('serve.salud.index', compact('establecimientos'));
    }

    public function create()
    {
        $this->autorizarAdministracion();

        $establecimiento = new SaludEstablecimiento();
        $categorias = self::CATEGORIAS_SERVICIO;

        return view('serve.salud.create', compact('establecimiento', 'categorias'));
    }

    public function store(SaludEstablecimientoRequest $request)
    {
        $this->autorizarAdministracion();

        $data = $this->datosEstablecimiento($request);
        $data['slug'] = $this->slugUnico($data['slug'] ?: $data['nombre']);

        $establecimiento = DB::transaction(function () use ($request, $data) {
            $establecimiento = SaludEstablecimiento::create($data);
            $this->sincronizarRelaciones($establecimiento, $request);

            SaludAuditoria::registrar('crear', 'establecimiento', $establecimiento->id, [], $establecimiento->fresh()->toArray());

            return $establecimiento;
        });

        return redirect()->route('salud.index')
            ->with('success', 'Establecimiento de salud creado correctamente.')
            ->with('establecimiento_creado', $establecimiento->id);
    }

    public function edit($id)
    {
        $establecimiento = $this->buscarPermitido($id);

        $establecimiento->load([
            'foto', 'telefonos', 'horarios', 'servicios', 'secciones',
            'cronogramasFilas',
            'cronogramasMensuales' => function ($q) {
                $q->where('eliminado', false)->orderBy('mes', 'desc');
            },
            'cronogramasMensuales.archivo',
        ]);

        $categorias = self::CATEGORIAS_SERVICIO;

        return view('serve.salud.edit', compact('establecimiento', 'categorias'));
    }

    public function update(SaludEstablecimientoRequest $request, $id)
    {
        $establecimiento = $this->buscarPermitido($id);

        $data = $this->datosEstablecimiento($request);
        $data['slug'] = $this->slugUnico($data['slug'] ?: $data['nombre'], $establecimiento->id);

        DB::transaction(function () use ($request, $establecimiento, $data) {
            $antes = $establecimiento->fresh()->toArray();

            $establecimiento->update($data);
            $this->sincronizarRelaciones($establecimiento, $request);

            SaludAuditoria::registrar(
                'editar',
                'establecimiento',
                $establecimiento->id,
                $antes,
                $establecimiento->fresh()->toArray()
            );
        });

        return redirect()->route('salud.index')
            ->with('success', 'Establecimiento actualizado correctamente.');
    }

    public function destroy($id)
    {
        $establecimiento = $this->buscarPermitido($id);

        $antes = $establecimiento->load(['telefonos', 'horarios', 'servicios', 'secciones'])->toArray();
        $establecimientoId = $establecimiento->id;

        $establecimiento->delete();

        SaludAuditoria::registrar('eliminar', 'establecimiento', $establecimientoId, $antes, []);

        return redirect()->route('salud.index')
            ->with('success', 'Establecimiento eliminado correctamente.');
    }

    public function publish($id)
    {
        $establecimiento = $this->buscarPermitido($id);

        $antes = $establecimiento->toArray();
        $establecimiento->activo = !$establecimiento->activo;
        $establecimiento->save();

        SaludAuditoria::registrar(
            $establecimiento->activo ? 'publicar' : 'despublicar',
            'establecimiento',
            $establecimiento->id,
            $antes,
            $establecimiento->fresh()->toArray()
        );

        return redirect()->back()->with(
            'success',
            $establecimiento->activo
                ? 'El establecimiento ahora es visible en el sitio público.'
                : 'El establecimiento fue ocultado del sitio público.'
        );
    }

    protected function datosEstablecimiento(SaludEstablecimientoRequest $request)
    {
        return [
            'nombre'          => $request->input('nombre'),
            'slug'            => $request->filled('slug') ? Str::slug($request->input('slug')) : null,
            'tipo'            => $request->input('tipo'),
            'nivel'           => $request->input('nivel'),
            'zona'            => $request->input('zona'),
            'direccion'       => $request->input('direccion'),
            'latitud'         => $request->filled('latitud') ? $request->input('latitud') : null,
            'longitud'        => $request->filled('longitud') ? $request->input('longitud') : null,
            'responsable'     => $request->input('responsable'),
            'descripcion'     => $request->input('descripcion'),
            'atiende_24h'     => (bool) $request->input('atiende_24h', false),
            'foto_fachada_id' => $request->filled('foto_fachada_id') ? $request->input('foto_fachada_id') : null,
            'activo'          => (bool) $request->input('activo', false),
        ];
    }

    protected function sincronizarRelaciones(SaludEstablecimiento $establecimiento, Request $request)
    {
        $establecimiento->telefonos()->delete();
        foreach ((array) $request->input('telefonos', []) as $orden => $telefono) {
            $numero = isset($telefono['numero']) ? trim($telefono['numero']) : '';
            if ($numero === '') {
                continue;
            }

            SaludTelefono::create([
                'establecimiento_id' => $establecimiento->id,
                'numero'             => $numero,
                'etiqueta'           => $this->etiquetaTelefono($telefono['etiqueta'] ?? 'fijo'),
                'orden'              => $orden,
            ]);
        }

        $establecimiento->horarios()->delete();
        foreach ((array) $request->input('horarios', []) as $horario) {
            $dias = array_map('intval', (array) ($horario['dias'] ?? []));
            $dias = array_values(array_filter($dias, function ($dia) {
                return $dia >= 1 && $dia <= 7;
            }));

            if (empty($dias) || empty($horario['hora_inicio']) || empty($horario['hora_fin'])) {
                continue;
            }

            SaludHorario::create([
                'establecimiento_id' => $establecimiento->id,
                'dias'               => array_values(array_unique($dias)),
                'hora_inicio'        => $horario['hora_inicio'],
                'hora_fin'           => $horario['hora_fin'],
                'nota'               => $horario['nota'] ?? null,
            ]);
        }

        $establecimiento->servicios()->delete();
        $orden = 0;
        foreach ((array) $request->input('servicios', []) as $servicio) {
            $nombre = isset($servicio['nombre']) ? trim($servicio['nombre']) : '';
            if ($nombre === '') {
                continue;
            }

            SaludServicio::create([
                'establecimiento_id' => $establecimiento->id,
                'nombre'             => $nombre,
                'categoria'          => $this->categoriaServicio($servicio['categoria'] ?? 'otro'),
                'descripcion'        => $servicio['descripcion'] ?? null,
                'orden'              => $orden++,
            ]);
        }

        $establecimiento->secciones()->delete();
        $orden = 0;
        foreach ((array) $request->input('secciones', []) as $seccion) {
            $titulo = isset($seccion['titulo']) ? trim($seccion['titulo']) : '';
            if ($titulo === '') {
                continue;
            }

            $items = $this->itemsDesdeTexto($seccion['items'] ?? null);

            SaludSeccionDetalle::create([
                'establecimiento_id' => $establecimiento->id,
                'titulo'             => $titulo,
                'texto'              => $seccion['texto'] ?? null,
                'items'              => !empty($items) ? $items : null,
                'orden'              => $orden++,
            ]);
        }

        $establecimiento->cronogramasFilas()->delete();
        $orden = 0;
        foreach ((array) $request->input('cronograma_filas', []) as $fila) {
            $valores = array_filter([
                trim((string) ($fila['dia_o_fecha'] ?? '')),
                trim((string) ($fila['servicio'] ?? '')),
                trim((string) ($fila['profesional'] ?? '')),
                trim((string) ($fila['horario'] ?? '')),
                trim((string) ($fila['observaciones'] ?? '')),
            ]);

            if (empty($valores)) {
                continue;
            }

            SaludCronogramaFila::create([
                'establecimiento_id' => $establecimiento->id,
                'dia_o_fecha'        => $fila['dia_o_fecha'] ?? null,
                'servicio'           => $fila['servicio'] ?? null,
                'profesional'        => $fila['profesional'] ?? null,
                'horario'            => $fila['horario'] ?? null,
                'observaciones'      => $fila['observaciones'] ?? null,
                'orden'              => $orden++,
            ]);
        }
    }

    protected function itemsDesdeTexto($texto)
    {
        if (!$texto) {
            return [];
        }

        $lineas = preg_split('/\r\n|\r|\n/', (string) $texto);

        return array_values(array_filter(array_map('trim', $lineas), function ($linea) {
            return $linea !== '';
        }));
    }

    protected function etiquetaTelefono($etiqueta)
    {
        return in_array($etiqueta, ['fijo', 'celular', 'whatsapp'], true) ? $etiqueta : 'fijo';
    }

    protected function categoriaServicio($categoria)
    {
        return array_key_exists($categoria, self::CATEGORIAS_SERVICIO) ? $categoria : 'otro';
    }

    protected function slugUnico($texto, $ignorarId = null)
    {
        $base = Str::slug($texto);
        if ($base === '') {
            $base = 'establecimiento-' . time();
        }

        $slug = $base;
        $contador = 2;

        while (SaludEstablecimiento::where('slug', $slug)
            ->when($ignorarId, function ($q) use ($ignorarId) {
                return $q->where('id', '!=', $ignorarId);
            })
            ->exists()) {
            $slug = $base . '-' . $contador;
            $contador++;
        }

        return $slug;
    }

    protected function consultaPermitida()
    {
        $query = SaludEstablecimiento::query();

        if (!$this->puedeAdministrarTodo()) {
            $ids = auth()->user()
                ? auth()->user()->establecimientosSalud()->pluck('salud_establecimientos.id')->all()
                : [];

            $query->whereIn('id', $ids);
        }

        return $query;
    }

    protected function buscarPermitido($id)
    {
        $establecimiento = SaludEstablecimiento::findOrFail($id);

        if (!$this->puedeAdministrarTodo()) {
            $asignado = auth()->user()
                && auth()->user()->establecimientosSalud()->where('salud_establecimientos.id', $id)->exists();

            if (!$asignado) {
                abort(403, 'No tienes acceso a este establecimiento de salud.');
            }
        }

        return $establecimiento;
    }

    protected function puedeAdministrarTodo()
    {
        $usuario = auth()->user();

        if (!$usuario) {
            return false;
        }

        return $usuario->can('salud.destroy') || $usuario->can('salud.auditoria');
    }

    protected function autorizarAdministracion()
    {
        if (!$this->puedeAdministrarTodo()) {
            abort(403, 'Solo un administrador de Salud puede crear o modificar establecimientos.');
        }
    }

    public static function mesesDelAnio()
    {
        $meses = [];
        for ($i = 1; $i <= 12; $i++) {
            $meses[sprintf('%02d', $i)] = Carbon::create(null, $i, 1)->format('F');
        }

        return $meses;
    }
}
