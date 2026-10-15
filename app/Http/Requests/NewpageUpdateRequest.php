<?php

namespace App\Http\Requests;

use App\Support\FacebookUrl;
use Illuminate\Foundation\Http\FormRequest;

class NewpageUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $value = trim((string) $this->input('facebook_url'));
        if ($value === '') {
            $this->merge(['facebook_url' => null]);
            return;
        }
        $this->merge(['facebook_url' => FacebookUrl::normalize($value)]);
    }

    public function rules()
    {
        $isEnlace = $this->input('tipo') === 'enlace';
        $newpage = $this->route('newpage');
        $hasPhoto = $newpage && trim((string) $newpage->photo) !== '';

        return [
            'user_id' => 'required|integer',
            'tipo' => 'required|in:completa,enlace',
            'titulo'  => 'required',
            'contenido' => 'nullable|max:10000',
            'facebook_url' => $isEnlace
                ? ['required', 'url', 'max:500', 'regex:/facebook\.com|fb\.watch|fb\.com/i']
                : ['nullable'],
            'photo' => ($isEnlace || $hasPhoto)
                ? 'nullable|max:5120|mimes:png,jpg,jpeg'
                : 'required|max:5120|mimes:png,jpg,jpeg',
        ];
    }

    public function messages()
    {
        return [
            'tipo.required' => 'Elija si es noticia completa o enlace',
            'titulo.required' => 'El campo titulo es obligatorio',
            'contenido.max' => 'Cantidad de caracter permitido 10000',
            'facebook_url.required' => 'Pegue el enlace o el código iframe de Facebook',
            'facebook_url.url' => 'Pegue el enlace o el código para incrustar de Facebook',
            'facebook_url.regex' => 'El enlace debe ser de Facebook',
            'photo.required' => 'La fotografía es obligatoria en la noticia completa',
            'photo.mimes' => 'Solo se admite los formatos jpg,jpeg y png',
            'photo.max' => 'La imagen debe pesar maximo 5MB',
        ];
    }
}
