<?php

namespace App\Http\Controllers\Salud;

use App\Http\Controllers\Controller;
use App\Salud\SaludArchivoManager;
use App\Salud\SaludAuditoria;
use Illuminate\Http\Request;

class SaludArchivoController extends Controller
{
    protected $manager;

    public function __construct(SaludArchivoManager $manager)
    {
        $this->manager = $manager;
    }

    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|max:10240',
        ], [
            'archivo.required' => 'Debes seleccionar un archivo.',
            'archivo.file'     => 'El archivo no es válido.',
            'archivo.max'      => 'El archivo no debe superar los 10 MB.',
        ]);

        $archivo = $this->manager->guardar($request->file('archivo'), optional(auth()->user())->id);

        SaludAuditoria::registrar('archivo', 'archivo', $archivo->id, [], [
            'nombre_original' => $archivo->nombre_original,
            'mime'            => $archivo->mime,
            'tamano'          => $archivo->tamano,
        ]);

        return response()->json([
            'id'     => $archivo->id,
            'url'    => $archivo->urlPublica(),
            'thumb'  => $archivo->urlThumb(),
            'opt'    => $archivo->urlOpt(),
            'mime'   => $archivo->mime,
            'nombre' => $archivo->nombre_original,
            'es_imagen' => $archivo->esImagen(),
        ]);
    }
}
