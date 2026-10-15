<?php

namespace App\Http\Controllers\Zoonosis;

use App\Document;
use App\Http\Controllers\Controller;
use App\Http\Requests\ZoonosisStoreRequest;
use App\Http\Requests\ZoonosisUpdateRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ZoonosisController extends Controller
{
    const CATEGORIE = 'ZOONOSIS';

    public function index()
    {
        $documents = Document::where('categorie', self::CATEGORIE)
            ->orderBy('date_creation', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('serve.zoonosis.index', compact('documents'));
    }

    public function create()
    {
        return view('serve.zoonosis.create');
    }

    public function store(ZoonosisStoreRequest $request)
    {
        $data = $request->except('file', 'cod', 'statu', 'date_creation', 'publish');
        $data['categorie'] = self::CATEGORIE;
        $data['publish'] = 1;
        $data['date_creation'] = now()->format('Y-m-d');

        $document = Document::create($data);

        if ($request->hasFile('file')) {
            $document->fill(['file' => $this->storeUpload($request->file('file'))])->save();
        }

        return redirect()->route('zoonosis.index')
            ->with('success', 'Actividad de Zoonosis creada con éxito');
    }

    public function show(Document $document)
    {
        $this->ensureZoonosis($document);

        return view('serve.zoonosis.show', compact('document'));
    }

    public function edit(Document $document)
    {
        $this->ensureZoonosis($document);

        return view('serve.zoonosis.edit', compact('document'));
    }

    public function update(ZoonosisUpdateRequest $request, Document $document)
    {
        $this->ensureZoonosis($document);

        $data = $request->except('file', 'cod', 'statu', 'date_creation', 'publish');
        $data['categorie'] = self::CATEGORIE;
        $data['publish'] = 1;
        $document->update($data);

        if ($request->hasFile('file')) {
            $document->fill(['file' => $this->storeUpload($request->file('file'))])->save();
        }

        return redirect()->route('zoonosis.index')
            ->with('success', 'Actividad de Zoonosis actualizada con éxito');
    }

    public function destroy(Document $document)
    {
        $this->ensureZoonosis($document);
        $document->delete();

        return back()->with('success', 'Eliminado correctamente');
    }

    public function publish(Document $document)
    {
        $this->ensureZoonosis($document);

        $document->publish = $document->publish == 1 ? 0 : 1;
        $document->save();

        return redirect()->back();
    }

    protected function ensureZoonosis(Document $document)
    {
        if ($document->categorie !== self::CATEGORIE) {
            abort(404);
        }
    }

    protected function storeUpload(UploadedFile $file)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $folder = $extension === 'pdf'
            ? 'server/documentos/zoonosis'
            : 'server/imagenes/zoonosis';

        return Storage::disk('public')->put($folder, $file);
    }
}
