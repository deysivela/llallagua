<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ZoonosisStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'categorie' => 'required',
            'entity' => 'required',
            'description' => 'required|max:1400',
            'file' => 'required|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    public function messages()
    {
        return [
            'categorie.required' => 'Debes elegir una categoria',
            'entity.required' => 'El titulo es obligatorio',
            'description.required' => 'El campo descripcion es obligatorio',
            'description.max' => 'La descripcion debe contener como maximo 1400 caracteres',
            'file.required' => 'Debe seleccionar un archivo',
            'file.mimes' => 'Formatos permitidos: PDF, JPG, JPEG o PNG',
            'file.max' => 'El archivo no debe superar los 5MB',
        ];
    }
}
