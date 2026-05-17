<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminUsersController extends Controller
{
    public function getUsers()
    {
        $users = User::withCount(['peticiones', 'firmas'])->get();
        return response()->json(['data' => $users]);
    }

    public function showUser($id)
    {
        $user = User::withCount(['peticiones', 'firmas'])->findOrFail($id);
        return response()->json(['data' => $user]);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $id,
            'role'     => 'required|in:user,admin',
            'password' => 'nullable|min:6',
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->role  = $request->role;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return response()->json(['message' => 'Usuario actualizado correctamente', 'data' => $user]);
    }

    public function createUser(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required|in:user,admin',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'role'     => $request->role,
        ]);

        return response()->json(['message' => 'Usuario creado correctamente', 'data' => $user], 201);
    }

    public function destroyUser($id)
    {
        $user = User::withCount(['peticiones', 'firmas'])->findOrFail($id);

        if ($user->peticiones_count > 0 || $user->firmas_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: El usuario tiene peticiones creadas o firmas activas.'
            ], 403);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'Usuario eliminado correctamente']);
    }
}
