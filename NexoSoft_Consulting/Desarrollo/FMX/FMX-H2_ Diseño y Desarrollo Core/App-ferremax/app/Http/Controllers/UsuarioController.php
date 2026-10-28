<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsuarioRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    // RF-28: Listado con buscador por nombre de usuario
    public function index(Request $request)
    {
        $buscar = $request->input('buscar', '');

        $usuarios = User::when($buscar, function ($query) use ($buscar) {
            $query->where('nombre_usuario', 'like', "%{$buscar}%");
        })
            ->orderBy('nombre_usuario')
            ->paginate(15)
            ->withQueryString();

        return view('usuarios.index', compact('usuarios', 'buscar'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    // RF-26: Crear usuario con contraseña hasheada
    public function store(UsuarioRequest $request)
    {
        $datos = $request->validated();
        $datos['password'] = Hash::make($datos['password']);
        $datos['activo'] = $request->boolean('activo');

        User::create($datos);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        return view('usuarios.edit', compact('usuario'));
    }

    // Editar rol y, opcionalmente, la contraseña
    public function update(UsuarioRequest $request, User $usuario)
    {
        $datos = $request->validated();

        if (! empty($datos['password'])) {
            $datos['password'] = Hash::make($datos['password']);
        } else {
            unset($datos['password']);
        }

        $datos['activo'] = $request->boolean('activo');

        // Un administrador no puede desactivarse a sí mismo desde el formulario.
        if ($usuario->id === Auth::id() && (! $datos['activo'] || $datos['rol'] !== 'admin')) {
            return back()->withInput()
                ->with('error', 'No puedes desactivar tu propia cuenta ni quitarte el rol administrador.');
        }

        $usuario->update($datos);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    // RF-27: "Eliminar" = desactivar (nunca borrado físico); permite reactivar.
    public function destroy(User $usuario)
    {
        if ($usuario->id === Auth::id()) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta ni quitarte el rol administrador.');
        }

        $usuario->update(['activo' => ! $usuario->activo]);

        $mensaje = $usuario->activo ? 'Usuario reactivado correctamente.' : 'Usuario desactivado correctamente.';

        return redirect()->route('usuarios.index')->with('success', $mensaje);
    }
}
