<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'level_id' => 'required',
            'charge_id' => [
                'required',
                Rule::exists('charges', 'id')->where('level_id', $this->input('level_id')),
            ],
            // 'salarie_id' => 'required',
            'name' => 'required',
            'last_name' => 'required',
            'ci' => 'nullable|string|max:30',
            'sex' => 'required',
            'address' => 'required',
            'photo' => 'nullable|max:8192|mimes:png,jpg,jpeg',
            'type_employee' => 'required',
        ]; 
        
    }

    public function messages()
    {
        return[
            'level_id.required' => 'Elija un nivel',
            'charge_id.required' => 'Elija un cargo',
            'charge_id.exists' => 'El cargo no pertenece al nivel seleccionado',
            // 'salarie_id.required' => 'Elija su salario',
            'name.required' => 'El campo nombre es obligatorio',
            'last_name.required' => 'El campo apellidos es obligatorio',
            'ci.max' => 'La cédula no puede superar 30 caracteres',
            'sex.required' => 'Seleccione su genero',
            'address.required' => 'El campo direccion es obligatorio',
            'photo.mimes' => 'Solo se admite los formatos jpg,jpeg y png',
            'photo.max' => 'La imagen no puede pesar más de 8 MB',
            'type_employee.required' => 'Seleccione el tipo de Empleado', 
        ];

    }
}
