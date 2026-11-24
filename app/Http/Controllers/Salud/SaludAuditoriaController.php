<?php

namespace App\Http\Controllers\Salud;

use App\Http\Controllers\Controller;
use App\Salud\SaludAuditoria;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SaludAuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = SaludAuditoria::with('usuario')->orderBy('fecha', 'desc')->orderBy('id', 'desc');

        if ($request->filled('entidad')) {
            $query->where('entidad', $request->input('entidad'));
        }

        if ($request->filled('accion')) {
            $query->where('accion', $request->input('accion'));
        }

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->input('usuario_id'));
        }

        if ($request->filled('desde')) {
            try {
                $query->where('fecha', '>=', Carbon::parse($request->input('desde'))->startOfDay());
            } catch (\Exception $e) {
                // Ignorar filtro inválido.
            }
        }

        if ($request->filled('hasta')) {
            try {
                $query->where('fecha', '<=', Carbon::parse($request->input('hasta'))->endOfDay());
            } catch (\Exception $e) {
                // Ignorar filtro inválido.
            }
        }

        $registros = $query->paginate(50)->appends($request->query());

        $entidades = SaludAuditoria::select('entidad')->distinct()->orderBy('entidad')->pluck('entidad');
        $acciones = SaludAuditoria::select('accion')->distinct()->orderBy('accion')->pluck('accion');

        return view('serve.salud.auditoria.index', compact('registros', 'entidades', 'acciones'));
    }
}
