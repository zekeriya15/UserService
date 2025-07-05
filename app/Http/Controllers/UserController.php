<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    
    public function me() 
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('division');

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'division' => $user->division ? [
                'id' => $user->division->id,
                'name' => $user->division->name,
            ] : null,
            'role' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'division_id'  => $request->division_id,
        ]);

        $role = Role::findOrFail($request->role_id);
        $user->assignRole($role->name);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
            'role' => $role->name 
        ], 201);
    }
}
