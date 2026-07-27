<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAtencionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cedula' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'nombre' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
            'apellido' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s\(\)]+$/'],
            'correo' => ['nullable', 'email:rfc,dns', 'max:150'],
            'institucion' => ['nullable', 'string', 'max:255'],
            'jefatura_destino_id' => ['required', 'exists:jefaturas,id'],
            'motivo' => ['required', 'string'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'cedula.regex' => 'La cédula solo debe contener números.',
            'nombre.regex' => 'El nombre solo puede contener letras y espacios.',
            'apellido.regex' => 'El apellido solo puede contener letras y espacios.',
            'telefono.regex' => 'El teléfono contiene caracteres no permitidos.',
        ];
    }
}
