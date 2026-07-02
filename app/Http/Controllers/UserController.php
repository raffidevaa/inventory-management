<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('manage-users');

        $users = User::with('role')->latest()->paginate(15);

        return view('users.index', compact('users'));
    }

    public function show(User $user)
    {
        $this->authorize('manage-users');

        $user->load('role');

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('manage-users');

        $roles = Role::all();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('manage-users');

        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
            'name' => ['required', 'string', 'max:100'],
        ]);

        // Prevent self-demotion
        if ($user->is(auth()->user()) && $user->hasRole('admin')) {
            $newRoleName = Role::find($validated['role_id'])?->name;
            if ($newRoleName !== 'admin') {
                return back()->withErrors(['role_id' => 'You cannot change your own admin role.']);
            }
        }

        // Prevent removing the last admin
        if ($user->hasRole('admin') && Role::find($validated['role_id'])?->name !== 'admin') {
            $adminCount = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->count();
            if ($adminCount <= 1) {
                return back()->withErrors(['role_id' => 'Cannot change role: this is the last admin account.']);
            }
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorize('manage-users');

        // Prevent deleting the last admin
        if ($user->hasRole('admin')) {
            $adminCount = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->count();
            if ($adminCount <= 1) {
                return back()->withErrors(['error' => 'Cannot delete the last admin account.']);
            }
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
