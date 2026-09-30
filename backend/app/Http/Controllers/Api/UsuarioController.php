<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

// RF12: Crear, editar y desactivar cuentas de usuario. Solo accesible por 'administrador'.
class UsuarioController extends Controller
{
    public function index()
    {
        return response()->json(Usuario::orderBy('nombre')->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:8',
            'rol' => 'required|in:administrador,vendedor,almacen',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
        ]);

        return response()->json($usuario, 201);
    }

    public function update(Request $request, Usuario $usuario)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|string|max:150',
            'email' => 'sometimes|email|unique:usuarios,email,' . $usuario->id_usuario . ',id_usuario',
            'rol' => 'sometimes|in:administrador,vendedor,almacen',
            'activo' => 'sometimes|boolean',
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $datos = $validator->validated();

        if (!empty($datos['password'])) {
            $datos['password'] = Hash::make($datos['password']);
        } else {
            unset($datos['password']);
        }

        $usuario->update($datos);

        return response()->json($usuario);
    }

    // Desactivar en vez de eliminar, para conservar trazabilidad (RF14)
    public function destroy(Usuario $usuario)
    {
        $usuario->update(['activo' => false]);

        return response()->json(['message' => 'Usuario desactivado.']);
    }
}
