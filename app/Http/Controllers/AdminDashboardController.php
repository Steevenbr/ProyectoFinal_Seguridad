<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // (opcional) buscador por nombre/email/rol
        $q = $request->query('q');

        $users = User::query()
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('role', 'like', "%{$q}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('dashboards.admin', compact('users', 'q'));
    }

    public function updateRole(Request $request, User $user)
    {
        // seguridad básica: evitar que el admin se cambie a sí mismo accidentalmente
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'No puedes cambiar tu propio rol.');
        }

        // roles permitidos
        $validated = $request->validate([
            'role' => ['required', 'in:usuario,tesorero,admin'],
        ]);

        $user->update(['role' => $validated['role']]);

        return back()->with('success', "Rol actualizado para {$user->name}.");
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $user->delete();

        return back()->with('success', 'Usuario eliminado.');
    }
}
