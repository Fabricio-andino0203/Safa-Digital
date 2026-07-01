<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6',
            'rol' => 'required|in:admin,empleado',
            'permisos' => 'nullable|array',
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'rol' => $validated['rol'],
            'permisos' => $validated['permisos'] ?? [],
        ]);

        return redirect()->route('configuracion.index')->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'rol' => 'required|in:admin,empleado',
            'permisos' => 'nullable|array',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'rol' => $validated['rol'],
            'permisos' => $user->id === 1 ? null : ($validated['permisos'] ?? []),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('configuracion.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy($id)
    {
        if ($id == 1) {
            return redirect()->route('configuracion.index')->with('error', 'No se puede eliminar el usuario administrador principal.');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('configuracion.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
