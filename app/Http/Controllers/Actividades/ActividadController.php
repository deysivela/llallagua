<?php

namespace App\Http\Controllers\Actividades;

use App\Document;
use App\Http\Controllers\Controller;
use App\Http\Requests\ActividadStoreRequest;
use App\Http\Requests\ActividadUpdateRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ActividadController extends Controller
{
    const CATEGORIE = 'ACTIVIDADES';

    public function index()
    {
        $documents = Document::where('categorie', self::CATEGORIE)
            ->orderBy('date_creation', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('serve.actividades.index', compact('documents'));
    }

    public function create()
    {
        return view('serve.actividades.create');
    }

    public function store(ActividadStoreRequest $request)
    {
        $data = $request->except('file', 'cod', 'statu', 'date_creation', 'publish');
        $data['categorie'] = self::CATEGORIE;
        $data['publish'] = 1;
        $data['date_creation'] = now()->format('Y-m-d');

        $document = Document::create($data);

        if ($request->hasFile('file')) {
            $document->fill(['file' => $this->storeUpload($request->file('file'))])->save();
        }

        return redirect()->route('actividad.index')
            ->with('success', 'Actividad creada con éxito');
    }

    public function show(Document $document)
    {
        $this->ensureActividad($document);

        return view('serve.actividades.show', compact('document'));
    }

    public function edit(Document $document)
    {
        $this->ensureActividad($document);

        return view('serve.actividades.edit', compact('document'));
    }

    public function update(ActividadUpdateRequest $request, Document $document)
    {
        $this->ensureActividad($document);

        $data = $request->except('file', 'cod', 'statu', 'date_creation', 'publish');
        $data['categorie'] = self::CATEGORIE;
        $data['publish'] = 1;
        $document->update($data);

        if ($request->hasFile('file')) {
            $document->fill(['file' => $this->storeUpload($request->file('file'))])->save();
        }

        return redirect()->route('actividad.index')
            ->with('success', 'Actividad actualizada con éxito');
    }

    public function destroy(Document $document)
    {
        $this->ensureActividad($document);
        $document->delete();

        return back()->with('success', 'Eliminado correctamente');
    }

    public function publish(Document $document)
    {
        $this->ensureActividad($document);

        $document->publish = $document->publish == 1 ? 0 : 1;
        $document->save();

        return redirect()->back();
    }

    protected function ensureActividad(Document $document)
    {
        if ($document->categorie !== self::CATEGORIE) {
            abort(404);
        }
    }

    protected function storeUpload(UploadedFile $file)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $folder = $extension === 'pdf'
            ? 'server/documentos/actividades'
            : 'server/imagenes/actividades';

        return Storage::disk('public')->put($folder, $file);
    }
}
