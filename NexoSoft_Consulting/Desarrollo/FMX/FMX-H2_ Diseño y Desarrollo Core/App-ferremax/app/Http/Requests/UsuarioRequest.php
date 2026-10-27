<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UsuarioRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['activo' => $this->boolean('activo')]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // En edición, la contraseña es opcional; en creación es obligatoria.
        $esCreacion = $this->isMethod('POST');

        return [
            'nombre_usuario' => [
                'required', 'string', 'max:60',
                Rule::unique('users', 'nombre_usuario')->ignore($this->route('usuario')),
            ],
            'password' => [$esCreacion ? 'required' : 'nullable', 'string', 'min:8', 'regex:/[0-9]/', 'confirmed'],
            'rol' => ['required', Rule::in(['admin', 'vendedor'])],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_usuario.required' => 'El nombre de usuario es obligatorio.',
            'nombre_usuario.unique' => 'Ese nombre de usuario ya está en uso.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'rol.required' => 'Debes seleccionar un rol.',
            'rol.in' => 'El rol debe ser administrador o vendedor.',
        ];
    }
}
