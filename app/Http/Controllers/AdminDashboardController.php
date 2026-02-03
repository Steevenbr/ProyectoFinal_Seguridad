<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditChain;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
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

    public function updateRole(Request $request, User $user, AuditChain $chain)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'No puedes cambiar tu propio rol.');
        }

        $validated = $request->validate([
            'role' => ['required', 'in:usuario,tesorero,admin'],
        ]);

        $oldRole = $user->role;
        $user->update(['role' => $validated['role']]);

        // ✅ Evento crítico: cambio de rol
        $chain->add(
            $request->user()->id,
            'role_changed',
            [
                'target_user_id' => $user->id,
                'target_email'   => $user->email,
                'old_role'       => $oldRole,
                'new_role'       => $validated['role'],
            ],
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', "Rol actualizado para {$user->name}.");
    }

    public function destroy(Request $request, User $user, AuditChain $chain)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $deletedInfo = [
            'target_user_id' => $user->id,
            'target_email'   => $user->email,
            'target_name'    => $user->name,
            'target_role'    => $user->role,
        ];

        $user->delete();

        // ✅ Evento crítico: eliminación de usuario
        $chain->add(
            $request->user()->id,
            'user_deleted',
            $deletedInfo,
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Usuario eliminado.');
    }
}
