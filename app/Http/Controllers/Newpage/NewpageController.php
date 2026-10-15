<?php

namespace App\Http\Controllers\Newpage;
use App\Http\Controllers\Controller;
use App\Newpage;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\NewpageStoreRequest;
use App\Http\Requests\NewpageUpdateRequest;
use App\Support\FacebookUrl;

class NewpageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $newpages = Newpage::all();
        return view('serve.newpage.index', compact('newpages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('serve.newpage.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(NewpageStoreRequest $request)
    {
        $data = $request->except(['photo', 'fecha', 'video_url', 'tipo']);
        $data['fecha'] = now()->toDateString();

        if ($request->input('tipo') === 'enlace') {
            $data['facebook_url'] = FacebookUrl::resolve($request->input('facebook_url'));
            $data['contenido'] = null;
            $data['photo'] = '';
        } else {
            $data['facebook_url'] = null;
            $data['contenido'] = $this->nullableHtml($request->input('contenido'));
            $data['photo'] = $request->file('photo')
                ? Storage::disk('public')->put('server/imagenes/noticias', $request->file('photo'))
                : '';
        }

        Newpage::create($data);

        return redirect()->route('newpages.index')->with('success', 'Noticia creada con éxito');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Newpage  $newpage
     * @return \Illuminate\Http\Response
     */
    public function show(Newpage $newpage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Newpage  $newpage
     * @return \Illuminate\Http\Response
     */
    public function edit(Newpage $newpage)
    {
        return view('serve.newpage.edit', compact('newpage'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Newpage  $newpage
     * @return \Illuminate\Http\Response
     */
    public function update(NewpageUpdateRequest $request, Newpage $newpage)
    {
        $data = $request->except(['photo', 'fecha', 'video_url', 'tipo']);

        if ($request->input('tipo') === 'enlace') {
            $data['facebook_url'] = FacebookUrl::resolve($request->input('facebook_url'));
            $data['contenido'] = null;
            $data['photo'] = '';
        } else {
            $data['facebook_url'] = null;
            $data['contenido'] = $this->nullableHtml($request->input('contenido'));
            if ($request->file('photo')) {
                $data['photo'] = Storage::disk('public')->put('server/imagenes/noticias', $request->file('photo'));
            }
        }

        $newpage->update($data);

        return redirect()->route('newpages.index')->with('success', 'Noticia actualizada con éxito');
    }

    public function destroy(Newpage $newpage)
    {
        $newpage->delete();
        return redirect()->route('newpages.index');
    }

    protected function nullableHtml($value)
    {
        $html = trim(html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8'));
        $plain = preg_replace('/\s+/u', '', strip_tags($html, '<img><iframe><video>'));
        $plain = str_replace("\xC2\xA0", '', $plain);

        return $plain === '' ? null : $html;
    }
}
