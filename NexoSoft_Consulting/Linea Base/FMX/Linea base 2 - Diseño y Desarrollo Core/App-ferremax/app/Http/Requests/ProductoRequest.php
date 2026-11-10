<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductoRequest extends FormRequest
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
        return [
            'nombre' => ['required', 'string', 'max:150', Rule::unique('productos', 'nombre')->where(fn ($q) => $q->where('proveedor', $this->input('proveedor'))->where('activo', true))->ignore($this->route('producto'))],
            'proveedor' => ['required', 'string', 'max:150'],
            'cantidad_stock' => [$this->isMethod('POST') ? 'required' : 'sometimes', 'integer', 'min:0', 'max:2147483647'],
            'precio_unitario' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'precio_costo' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'categoria' => ['required', 'string', 'max:80'],
            'ubicacion' => ['nullable', 'string', 'max:80'],
            'stock_minimo' => [$this->isMethod('POST') ? 'required' : 'sometimes', 'integer', 'min:0', 'max:2147483647'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'proveedor.required' => 'El proveedor es obligatorio.',
            'cantidad_stock.min' => 'El stock no puede ser negativo.',
            'precio_unitario.min' => 'El precio debe ser mayor a 0.',
            'precio_unitario.max' => 'El precio no puede superar 999,999.99.',
            'precio_costo.required' => 'El precio de costo es obligatorio.',
            'precio_costo.min' => 'El precio de costo no puede ser negativo.',
            'precio_costo.max' => 'El precio de costo no puede superar 999,999.99.',
            'stock_minimo.min' => 'El stock mínimo no puede ser negativo.',
        ];
    }
}
